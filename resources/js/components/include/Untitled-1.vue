<div class="row pt-3">
                    <div class="col-12 col-xs-12 col-sm-3 col-md-12 col-lg-3 mb-1" style="font-size:12px;" v-for="item  in items[current.body]">
                        <div class="small-box text-white" :class="[item.is_un_assigned===true ? 'bg-warning' : item.is_new ? 'bg-success' : 'bg-danger']">
                            <div class="inner">
<h5 class="mb-3" style="font-size: 15px;">
    <span v-if="item.is_new">
    <span class="fas fa-fw fa-plus-square"></span>
    New {{ constants.bodies[item.body] }} Reservation  - {{ item.meet ? item.meet.name : item.meetName}}
    </span>
    <span v-else>
    <span class="fas fa-fw fa-pen-square"></span>
    {{ constants.bodies[item.body] }} Reservation Update - {{ item.meet ? item.meet.name : item.meetName }}
    </span>
    </h5>
    <div class="" style="letter-spacing: 0.5px">
    <div class="">
    <strong>Sanction No.:</strong> {{ item.sanction }}
    </div>
    <div class="">
    <strong>Gym:</strong> {{ item.gym.name }}
    </div>
    <div class="">
    <strong>Category:</strong> {{ item.category.name }}
    </div>
    <div class="">
    <strong>Type:</strong>
    <span class="badge badge-pill badge-success" v-if="item.is_new">New Reservation</span>
    <span class="badge badge-pill badge-warning" v-if="item.has_update">Details Updated</span>
    <span class="badge badge-pill badge-danger" v-if="item.has_deletion">Reservation Removed</span>
    </div>
    <div class="">
    <strong>Last updated:</strong> {{ item.readable.last_updated }}
    </div>
    </div> 
                            </div>
                            <a href="#" v-if="item.is_un_assigned===true" class="small-box-footer mt-2 small-box-footer-font">
                                <b>Successfully received your USAG Reservation, however the host has not made this meet active on AllGym. You cannot complete registration until this is done.</b>
                            </a>
                            <a :href='item.url' v-else class="small-box-footer mt-2">
                                <b>View Details &amp; Merge</b> <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
