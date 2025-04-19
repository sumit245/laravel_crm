<div class="tab-content mt-3" id="myTabContent">
              <ul class="nav nav-tabs fixed-navbar-project" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                  <button class="nav-link active" id="assigned-tab" data-bs-toggle="tab" data-bs-target="#assigned"
                    type="button" role="tab" aria-controls="assigned" aria-selected="true">
                    Assigned Tasks
                  </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="surveyed-tab" data-bs-toggle="tab" data-bs-target="#surveyed"
                    type="button" role="tab" aria-controls="surveyed" aria-selected="true">
                    surveyed Poles
                  </button>
                 </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="vendors-tab" data-bs-toggle="tab" data-bs-target="#vendors" type="button"
                    role="tab" aria-controls="vendors" aria-selected="true">
                    Installed Lights
                  </button>
                </li>
                <li class="nav-item" role="presentation">
                  <button class="nav-link" id="inventory-tab" data-bs-toggle="tab" data-bs-target="#inventory"
                    type="button" role="tab" aria-controls="inventory" aria-selected="false">
                    Rejected Tasks
                  </button>
                </li>
              </ul>

              {{-- For Streetlight Projects --}}
              <!-- Begin -->
              <div class="tab-pane fade show active" id="assigned" role="tabpanel" aria-labelledby="assigned-tab">
                 @include("staff.assignedTasks")
              </div>

              <!-- Staffs Tab -->
              <div class="tab-pane fade" id="surveyed" role="tabpanel" aria-labelledby="surveyed-tab">
               @include("staff.surveyedPoles")
              </div>

              <!-- Vendors Tab -->
              <div class="tab-pane fade" id="vendors" role="tabpanel" aria-labelledby="vendors-tab">
               <h1>Hello</h1>
              </div>

              <!-- Inventory Tab -->
              <div class="tab-pane fade" id="inventory" role="tabpanel" aria-labelledby="inventory-tab">
              <h1>Hell000o</h1>
              </div>
              <!-- End -->
          </div>