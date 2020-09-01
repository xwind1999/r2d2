from locust import HttpUser, TaskSet, task
import json
import time
import random
import string
from datetime import datetime, timedelta
import os
import broadcasts
import quickdata


class Broadcasts(HttpUser):
    tasks = {
        broadcasts.broadcast_partner: 1,
        broadcasts.broadcast_product_box: 2,
        broadcasts.broadcast_product_experience: 2,
        broadcasts.broadcast_product_relationship: 1,
        broadcasts.broadcast_price_relationship: 1,
        broadcasts.broadcast_room_availability: 1,
        broadcasts.broadcast_room_price: 1
    }
    min_wait = 5000
    max_wait = 9000

class QuickData(HttpUser):
    tasks = {
        quickdata.quickdata_getrangev2: 1,
        quickdata.quickdata_getpackage: 1,
        quickdata.quickdata_getpackagev2: 1,
        quickdata.quickdata_availability_price_period: 1,
    }
    min_wait = 5000
    max_wait = 9000