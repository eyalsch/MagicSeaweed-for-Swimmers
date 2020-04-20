#include <TimeLib.h>
#include "Arduino.h"
#include <ArduinoJson.h>
#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <WiFiClient.h>
#include <Adafruit_GFX.h>    // Core graphics library
#include <Adafruit_ST7735.h> // Hardware-specific library for ST7735
#include <SPI.h>

const char* ssid     = "YOUR SSID";     // SSID of local network
const char* password = "YOUR PASSWORD";   // Password on network

#define TFT_CS     D1
#define TFT_RST    D0
#define TFT_DC     D2

Adafruit_ST7735 tft = Adafruit_ST7735(TFT_CS,  TFT_DC, TFT_RST);

void setup() {
  Serial.begin(115200);
  tft.initR(INITR_BLACKTAB);   // initialize a ST7735S chip, black tab
  tft.fillScreen(ST77XX_BLACK);
  tft.setRotation(1);
  tft.setCursor(0, 0);
  tft.setTextColor(ST77XX_RED);
  tft.setTextWrap(true);
  tft.setTextSize(2);
  tft.println("Magic Seaweed For Swimmers");
  Serial.println("Connecting WiFi");
  WiFi.begin(ssid, password);

  while (WiFi.status() != WL_CONNECTED) {
    delay(300);
    Serial.print(".");
    tft.print(".");
  }
  Serial.println("");
  Serial.print("MyIP: "); Serial.println(WiFi.localIP());

  WiFiClient client;
  HTTPClient http;

  http.begin(client, "http://magicseaweed.com/api/YOUR API KEY/forecast/?spot_id=3978&units=eu&fields=localTimestamp,swell.minBreakingHeight,swell.maxBreakingHeight,swell.components.combined.height,swell.components.combined.period,wind.speed,wind.direction");
  int httpCode = http.GET();
  Serial.print("httpCode: "); Serial.println(httpCode);
  String JSONMessage = http.getString();
  //Serial.println(JSONMessage);

  http.end();
  Serial.println("Parsing start:");
  MSWparserr(JSONMessage);
  Serial.println("End");
}

void loop() {

}

void MSWparserr(String msg) {
  //Serial.println(msg);
  DynamicJsonDocument JSONBuffer(15000); //Memory pool
  DeserializationError error = deserializeJson(JSONBuffer, msg);
  if (error) { //Check for errors in parsing
    Serial.print(F("deserializeJson() failed with code "));
    Serial.println(error.c_str());
    return;
  }
  Serial.print("measureLength: ");
  Serial.println(measureJson(JSONBuffer));
  Serial.print("size: ");
  Serial.println(JSONBuffer.size());

  Serial.println("Time\t\t\tMin-Max\tSwell\t\tWind");
  for (int i = 0; i < JSONBuffer.size(); i++) {
    time_t timestamp = JSONBuffer[i]["localTimestamp"];
    float minBreakingHeight = JSONBuffer[i]["swell"]["minBreakingHeight"];
    float maxBreakingHeight = JSONBuffer[i]["swell"]["maxBreakingHeight"];
    float swellHeight = JSONBuffer[i]["swell"]["components"]["combined"]["height"];
    float swellPeriod = JSONBuffer[i]["swell"]["components"]["combined"]["period"];
    float windSpeed = JSONBuffer[i]["wind"]["speed"];
    float windDirection = JSONBuffer[i]["wind"]["direction"];
    //Serial.print(timestamp);
    printDigits(day(timestamp));   Serial.print("/");
    printDigits(month(timestamp)); Serial.print(" ");
    printDigits(hour(timestamp));  Serial.print(":");
    printDigits(minute(timestamp));
    Serial.print("\t\t");
    Serial.print(minBreakingHeight, 1);
    Serial.print('-');
    Serial.print(maxBreakingHeight, 1);
    Serial.print("\t");
    Serial.print(swellHeight, 1);
    Serial.print('/');
    Serial.print(int(swellPeriod));
    Serial.print("\t\t");
    Serial.print(int(windSpeed));
    Serial.print('/');
    Serial.println(int(windDirection));
  }
  time_t tomorrow = JSONBuffer[10]["localTimestamp"];
  String minH = JSONBuffer[10]["swell"]["minBreakingHeight"];
  String maxH = JSONBuffer[10]["swell"]["maxBreakingHeight"];
  String swellH = JSONBuffer[10]["swell"]["components"]["combined"]["height"];
  String swellP = JSONBuffer[10]["swell"]["components"]["combined"]["period"];
  String windSpd = JSONBuffer[10]["wind"]["speed"];
  String windDir = JSONBuffer[10]["wind"]["direction"];

  tft.fillScreen(ST77XX_BLACK);
  tft.setCursor(0, 0);

  tftDigits(day(tomorrow));   tft.print("/");
  tftDigits(month(tomorrow)); tft.print(" ");
  tftDigits(hour(tomorrow));  tft.print(":");
  tftDigits(minute(tomorrow));  tft.println("");
  
  tft.setTextSize(2);
  tft.setTextColor(ST77XX_BLUE);
  tft.println("Waves:");
  tft.setTextColor(ST77XX_WHITE);
  tft.println(minH + "m - " + maxH + "m");
  tft.setTextColor(ST77XX_BLUE);
  tft.println("Swell:");
  tft.setTextColor(ST77XX_WHITE);
  tft.println(swellH + "m in " + swellP + "sec");
  tft.setTextColor(ST77XX_BLUE);
  tft.println("Wind:");
  tft.setTextColor(ST77XX_WHITE);
  tft.print(windSpd + "kmh from " + windDir);
}

void printDigits(int digits) {
  // utility for digital clock display: prints preceding colon and leading 0
  if (digits < 10) Serial.print('0');
  Serial.print(digits);
}

void tftDigits(int digits) {
  // utility for digital clock display: prints preceding colon and leading 0
  if (digits < 10) tft.print("0");
  tft.print(digits);
}
