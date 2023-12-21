[x] Prepare migrations 
[x] Seed the initial tags 
[x] Prepare factories 
[x] Prepare resource 
[x] Tags 
    - Routes
    - Controller
    - Tests

### list Offices Endpoint 
[x] Show only approved and visible records 
[x] Filter by hosts 
[x] Filter by users 
[x] Include tags , images and user
[x] Show count of previous reservations 
[x] Paginate 
[] Sort by distance if lng/lat provided , Otherwise , oldest first 

### Show office endpoint 

[x] Show count of previous reservations 
[x] Include tags , images and user

### List Offices Endpoint

[x] Change the user_id filter to visitor_id and host_id to user_id 
[x] Switch to using Custom Polymorphic Types 
[] Order by distance but Don`t Include the distance attribute 
[x] Configure the resources 

#### Create office endpoint 

[x] Host must be authenicated & email verfied 
[x] Token (if exists) must allow `office.create`
[x] Validation 

# TODO 

[x] office approval status should be pending or approved only ... no rejected
[x] store office inside a database transaction

## Update Office Endpoint 

[x] Must be authenticated & email verified 
[x] Token (if exists) must allow `office.update`
[x] Can only update their own offices
[x] Validation 
[x] Mark as pending when critical attributes are updated and notify admin 

## Create Office Endpoint 

[x] Notify admin on a new office

## Delete Office Endpoint 

[x] Must be authenticated & email verified
[x] Token (if exists) must allowa `office.delete`
[x] Can only delete their own offices
[x] Cannot delete an office that has reservation 

### Office Photo

[x] Attaching photos to an office 
[x] Allow choosing a photo t become the featured photo 
[x] Deleting a photo 
- Must have at least one photo if it`s approved

## List Reservations Endponit 

[x] Must be authenticated & email verified
[x] Token (if exists) must allow `reservations.show`
[x] Can only list their own reservations or reservations on their offices
[x] Allow filtering by office_id
[x] Allow filtering by user_id
[x] Allow filtering by date range 
[x] Paginate

## Make Reservations Endpint 

[] Must be authenticated & email verified
[] Token (if exists) must allow `reservations.make`
[] Cannot make reservations on their own property
[] Validate on other reservation conflict with the same time 
[] Use locks to make the process atomic 
[] Email user & host when a reservation is made 
[] Email user & host on a reservation start day 
[] Generate WIFI passord for new reservations (store encrypted)

## Cancel Reservation Endpoint 

[] Must be authenticated & email verified
[] Token (if exists) must allow `reservations.cancel`
[] Can only cancel their own reservation 
[] CAn only cancel active reservation that has a start_date in the future












