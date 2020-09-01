from locust import HttpUser, TaskSet, task
import json
import time
import random
import string
from datetime import datetime, timedelta
import os

expected_time = 0.3
partnerId = random.randint(1,8000000)
boxProductId = random.randint(1,800000)
experienceProductId = random.randint(1,800000)
componentProductId = random.randint(1,800000)

now = datetime.now()
date_from = datetime.strftime(now + timedelta(days = 5), "%Y-%m-%d")+"T20:00:00.000000+0000"
date_to = datetime.strftime(now + timedelta(days = 10), "%Y-%m-%d")+"T20:00:00.000000+0000"
update_at = datetime.strftime(now, "%Y-%m-%d")+"T12:00:00.000000+0000"

def broadcast_partner(self):
   headers = {'content-type': 'application/json','Authorization':'Basic ZWFpOmVhaQ=='}
   response = self.client.post("/broadcast-listener/partner",data= json.dumps({
   "id": partnerId,
   "status" : "partner",
   "currencyCode" : "EUR",
   "isChannelManagerEnabled" : False
   }),
   headers=headers,
   name = "Broadcast Partner to R2D2")
   try:
       assert response.elapsed.total_seconds() < expected_time, "Request Broadcast Partner took %r which is more than %r seconds" % (response.elapsed.total_seconds(), expected_time)
       print ("Response code", response.status_code)
       assert response.status_code == 202
   except AssertionError as err:
       print(err)

def broadcast_product_box(self):
   headers = {'content-type': 'application/json','Authorization':'Basic ZWFpOmVhaQ=='}
   response = self.client.post("/broadcast-listener/product",data= json.dumps({
   "id": boxProductId,
   "name": "Load Test Box",
   "status": "inactive",
   "currencyCode": "EUR",
   "isSellable" : False,
   "isReservable" : False,
   "type": "mev",
   "partner":{
     "id":partnerId
    },
   "sellableBrand" : {
     "code":"SBX"
    },
   "sellableCountry":{
     "code":"FR"
    },
   "listPrice": {
     "currencyCode": "EUR",
     "amount" : 100
     }
   }),
   headers=headers,
   name = "Broadcast Product Box to R2D2")
   try:
       assert response.elapsed.total_seconds() < expected_time, "Request Broadcast Product Type Box took %r which is more than %r seconds" % (response.elapsed.total_seconds(), expected_time)
       print ("Response code", response.status_code)
       assert response.status_code == 202
   except AssertionError as err:
       print(err)

def broadcast_product_experience(self):
   headers = {'content-type': 'application/json','Authorization':'Basic ZWFpOmVhaQ=='}
   response = self.client.post("/broadcast-listener/product",data= json.dumps({
   "id": experienceProductId,
   "name": "Load Test Experience",
   "status": "inactive",
   "currencyCode": "EUR",
   "isSellable" : False,
   "isReservable" : False,
   "type": "experience",
   "partner":{
     "id":partnerId
    },
   "sellableBrand" : {
     "code":"SBX"
    },
   "sellableCountry":{
     "code":"FR"
    },
   "listPrice": {
     "currencyCode": "EUR",
     "amount" : 100
     }
   }),
   headers=headers,
   name = "Broadcast Product Experience to R2D2")
   try:
       assert response.elapsed.total_seconds() < expected_time, "Request Broadcast Product Experience type took %r which is more than %r seconds" % (response.elapsed.total_seconds(), expected_time)
       print ("Response code", response.status_code)
       assert response.status_code == 202
   except AssertionError as err:
       print(err)

def broadcast_product_relationship(self):
   headers = {'content-type': 'application/json','Authorization':'Basic ZWFpOmVhaQ=='}
   response = self.client.post("/broadcast-listener/product-relationship",data= json.dumps({
   "parentProduct": boxProductId,
   "childProduct" : experienceProductId,
   "relationshipType" : "Box-Experience",
   "isEnabled" : False
   }),
   headers=headers,
   name = "Broadcast Box Experience Relationship to R2D2")
   try:
       assert response.elapsed.total_seconds() < expected_time, "Request Broadcast Box Experience took %r which is more than %r seconds" % (response.elapsed.total_seconds(), expected_time)
       print ("Response code", response.status_code)
       assert response.status_code == 202
   except AssertionError as err:
       print(err)

def broadcast_price_relationship(self):
   headers = {'content-type': 'application/json','Authorization':'Basic ZWFpOmVhaQ=='}
   response = self.client.post("/broadcast-listener/price-information",data= json.dumps({
   "product": {
     "id":experienceProductId
   },
   "averageValue" : {
     "amount" : 100,
     "currencyCode" : "EUR"
   },
   "averageCommissionType" : "percentage",
   "averageCommission" : 10
   }),
   headers=headers,
   name = "Broadcast Price Information to R2D2")
   try:
       assert response.elapsed.total_seconds() < expected_time, "Request Broadcast Price Information took %r which is more than %r seconds" % (response.elapsed.total_seconds(), expected_time)
       print ("Response code", response.status_code)
       assert response.status_code == 202
   except AssertionError as err:
       print(err)

def broadcast_room_availability(self):
  headers = {'Authorization': 'Basic YWRtaW46YWRtaW4=','Content-Type': 'application/json'}
  response = self.client.post("/broadcast-listener/room-availability", data = json.dumps([
    {
      "product": {
        "id": componentProductId
      },
      "quantity": 1,
      "dateFrom": date_from,
      "dateTo": date_to,
      "updatedAt": update_at
    }
  ]),
  headers = headers,
  name = "Broadcast Room Availability to R2D2")
  try:
       assert response.elapsed.total_seconds() < expected_time, "Request Broadcast Room Availability took %r which is more than %r seconds" % (response.elapsed.total_seconds(), expected_time)
       print ("Response code", response.status_code)
       assert response.status_code == 202
  except AssertionError as err:
       print(err)

def broadcast_room_price(self):
  headers = {'Authorization': 'Basic YWRtaW46YWRtaW4=','Content-Type': 'application/json'}
  response = self.client.post("/broadcast-listener/room-price", data = json.dumps([
    {
      "product": {
        "id": componentProductId
      },
      "dateFrom": date_from,
      "dateTo": date_to,
      "updatedAt": update_at,
      "price": {
        "amount": 10.5,
        "currencyCode": "EUR"
      }
    }
  ]),
  headers = headers,
  name = "Broadcast Room Price to R2D2")
  try:
       assert response.elapsed.total_seconds() < expected_time, "Request Broadcast Room Price took %r which is more than %r seconds" % (response.elapsed.total_seconds(), expected_time)
       print ("Response code", response.status_code)
       assert response.status_code == 202
  except AssertionError as err:
       print(err)
