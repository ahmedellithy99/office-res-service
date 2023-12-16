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

[] Host must be authenicated & email verfied 
[] Token (if exists) must allow `office.create`
[] Validation 

### Office Photo

[] Attaching photos to an office 
[] Allow choosing a photo t become the featured photo 
[] Deleting a photo 
- Must have at least one photo if it`s approved

### Update Office Endpoint

[] Must be authenticated & email verified 
[] Token (if exists) must allow `office.update`
[] Can only update their offices
[] Validation 

## Delete Office Endponit 

[] Must be authenticated & email verified 
[] Token (if exists) must allow `office.delete`
[] Can only delete their own offices

## List Reservations Endponit 

[] Must be authenticated & email verified
[] Token (if exists) must allow `reservations.show`
[] Can only lost their own reservations or reservatons on their offices
[] Allow filtering by office_id
[] Allow filtering by user_id
[] Allow filtering by data range 
[] Allow filtering by status
[] Paginate

## Make Reservations Endpint 

[] Must be authenticated & email verified
[] Token (if exists) must allow `reservations.make`
[] Cannot make reservations on their own property
[] Validate on other reservation conflict with the same time 
[] Use locks to make the process atomic 
[] Email user & host when a reservation is made 
[] Email user & host on a reservation start day 
[] Generate WIFI passord for new reservations (store encrypted)

## Cance Reservation Endpoint 

[] Must be authenticated & email verified
[] Token (if exists) must allow `reservations.cancel`
[] Can only cancel their own reservation 
[] CAn only cancel active reservation that has a start_date in the future












