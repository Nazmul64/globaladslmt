@extends('agent.master')

@section('content')
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
  <h6 class="fw-semibold mb-0">Dashboard</h6>
  <ul class="d-flex align-items-center gap-2">
    <li class="fw-medium">
      <a href="index.html" class="d-flex align-items-center gap-1 hover-text-primary">
        <iconify-icon icon="solar:home-smile-angle-outline" class="icon text-lg"></iconify-icon>
        Dashboard
      </a>
    </li>
    <li>-</li>
    <li class="fw-medium">AI</li>
  </ul>
</div>

    <div class="row row-cols-xxxl-5 row-cols-lg-3 row-cols-sm-2 row-cols-1 gy-4">
      <div class="col">
        <div class="card shadow-none border bg-gradient-start-1 h-100">
          <div class="card-body p-20">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
              <div>
                <p class="fw-medium text-primary-light mb-1">Total Users</p>
                <h6 class="mb-0">20</h6>
              </div>
              <div class="w-50-px h-50-px bg-cyan rounded-circle d-flex justify-content-center align-items-center">
                <iconify-icon icon="gridicons:multiple-users" class="text-white text-2xl mb-0"></iconify-icon>
              </div>
            </div>
          </div>
        </div><!-- card end -->
      </div>
      <div class="col">
        <div class="card shadow-none border bg-gradient-start-2 h-100">
          <div class="card-body p-20">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
              <div>
                <p class="fw-medium text-primary-light mb-1">Blocked User</p>
                <h6 class="mb-0">0</h6>
              </div>
              <div class="w-50-px h-50-px bg-purple rounded-circle d-flex justify-content-center align-items-center">
                <iconify-icon icon="fa-solid:award" class="text-white text-2xl mb-0"></iconify-icon>
              </div>
            </div>
          </div>
        </div><!-- card end -->
      </div>
      <div class="col">
        <div class="card shadow-none border bg-gradient-start-3 h-100">
          <div class="card-body p-20">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
              <div>
                <p class="fw-medium text-primary-light mb-1">Blance </p>
                <h6 class="mb-0">5,000 Coin</h6>
              </div>
              <div class="w-50-px h-50-px bg-info rounded-circle d-flex justify-content-center align-items-center">
                <iconify-icon icon="fluent:people-20-filled" class="text-white text-2xl mb-0"></iconify-icon>
              </div>
            </div>
          </div>
        </div><!-- card end -->
      </div>
      <div class="col">
        <div class="card shadow-none border bg-gradient-start-4 h-100">
          <div class="card-body p-20">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
              <div>
                <p class="fw-medium text-primary-light mb-1">Payments 1</p>
                <h6 class="mb-0">420</h6>
              </div>
              <div class="w-50-px h-50-px bg-success-main rounded-circle d-flex justify-content-center align-items-center">
                <iconify-icon icon="solar:wallet-bold" class="text-white text-2xl mb-0"></iconify-icon>
              </div>
            </div>
          </div>
        </div><!-- card end -->
      </div>
      <div class="col">
        <div class="card shadow-none border bg-gradient-start-5 h-100">
          <div class="card-body p-20">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
              <div>
                <p class="fw-medium text-primary-light mb-1">Paid</p>
                <h6 class="mb-0">420 Coin</h6>
              </div>
              <div class="w-50-px h-50-px bg-red rounded-circle d-flex justify-content-center align-items-center">
                <iconify-icon icon="fa6-solid:file-invoice-dollar" class="text-white text-2xl mb-0"></iconify-icon>
              </div>
            </div>

          </div>
        </div><!-- card end -->
      </div>
       <div class="col">
        <div class="card shadow-none border bg-gradient-start-5 h-100">
          <div class="card-body p-20">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
              <div>
                <p class="fw-medium text-primary-light mb-1">Pending </p>
                <h6 class="mb-0">0 Coin</h6>
              </div>
              <div class="w-50-px h-50-px bg-red rounded-circle d-flex justify-content-center align-items-center">
                <iconify-icon icon="fa6-solid:file-invoice-dollar" class="text-white text-2xl mb-0"></iconify-icon>
              </div>
            </div>

          </div>
        </div><!-- card end -->
      </div>
       <div class="col">
        <div class="card shadow-none border bg-gradient-start-5 h-100">
          <div class="card-body p-20">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
              <div>
                <p class="fw-medium text-primary-light mb-1">Tody Clicks</p>
                <h6 class="mb-0">420 </h6>
              </div>
              <div class="w-50-px h-50-px bg-red rounded-circle d-flex justify-content-center align-items-center">
                <iconify-icon icon="fa6-solid:file-invoice-dollar" class="text-white text-2xl mb-0"></iconify-icon>
              </div>
            </div>

          </div>
        </div><!-- card end -->
      </div>
       <div class="col">
        <div class="card shadow-none border bg-gradient-start-5 h-100">
          <div class="card-body p-20">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
              <div>
                <p class="fw-medium text-primary-light mb-1">Invalid Clicks</p>
                <h6 class="mb-0">42 </h6>
              </div>
              <div class="w-50-px h-50-px bg-red rounded-circle d-flex justify-content-center align-items-center">
                <iconify-icon icon="fa6-solid:file-invoice-dollar" class="text-white text-2xl mb-0"></iconify-icon>
              </div>
            </div>

          </div>
        </div><!-- card end -->
      </div>
    </div>

    <div class="row gy-4 mt-1">
      <div class="col-xxl-12 col-xl-12">
        <div class="card h-100">
            <div class="card-body p-24">
              <div class="tab-content" id="pills-tabContent">
                <div class="tab-pane fade show active" id="pills-to-do-list" role="tabpanel" aria-labelledby="pills-to-do-list-tab" tabindex="0">
                  <div class="table-responsive scroll-sm">
                    <table class="table bordered-table sm-table mb-0">
                      <thead>
                        <tr>
                          <th scope="col">Users </th>
                          <th scope="col">Registered On</th>
                          <th scope="col">Plan</th>
                          <th scope="col" class="text-center">Status</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <td>
                            <div class="d-flex align-items-center">
                              <img src="assets/images/users/user1.png" alt="" class="w-40-px h-40-px rounded-circle flex-shrink-0 me-12 overflow-hidden">
                              <div class="flex-grow-1">
                                <h6 class="text-md mb-0 fw-medium">Dianne Russell</h6>
                                <span class="text-sm text-secondary-light fw-medium">redaniel@gmail.com</span>
                              </div>
                            </div>
                          </td>
                          <td>27 Mar 2024</td>
                          <td>Free</td>
                          <td class="text-center">
                            <span class="bg-success-focus text-success-main px-24 py-4 rounded-pill fw-medium text-sm">Active</span>
                          </td>
                        </tr>
                        <tr>
                          <td>
                            <div class="d-flex align-items-center">
                              <img src="assets/images/users/user2.png" alt="" class="w-40-px h-40-px rounded-circle flex-shrink-0 me-12 overflow-hidden">
                              <div class="flex-grow-1">
                                <h6 class="text-md mb-0 fw-medium">Wade Warren</h6>
                                <span class="text-sm text-secondary-light fw-medium">xterris@gmail.com</span>
                              </div>
                            </div>
                          </td>
                          <td>27 Mar 2024</td>
                          <td>Basic</td>
                          <td class="text-center">
                            <span class="bg-success-focus text-success-main px-24 py-4 rounded-pill fw-medium text-sm">Active</span>
                          </td>
                        </tr>
                        <tr>
                          <td>
                            <div class="d-flex align-items-center">
                              <img src="assets/images/users/user3.png" alt="" class="w-40-px h-40-px rounded-circle flex-shrink-0 me-12 overflow-hidden">
                              <div class="flex-grow-1">
                                <h6 class="text-md mb-0 fw-medium">Albert Flores</h6>
                                <span class="text-sm text-secondary-light fw-medium">seannand@mail.ru</span>
                              </div>
                            </div>
                          </td>
                          <td>27 Mar 2024</td>
                          <td>Standard</td>
                          <td class="text-center">
                            <span class="bg-success-focus text-success-main px-24 py-4 rounded-pill fw-medium text-sm">Active</span>
                          </td>
                        </tr>
                        <tr>
                          <td>
                            <div class="d-flex align-items-center">
                              <img src="assets/images/users/user4.png" alt="" class="w-40-px h-40-px rounded-circle flex-shrink-0 me-12 overflow-hidden">
                              <div class="flex-grow-1">
                                <h6 class="text-md mb-0 fw-medium">Bessie Cooper </h6>
                                <span class="text-sm text-secondary-light fw-medium">igerrin@gmail.com</span>
                              </div>
                            </div>
                          </td>
                          <td>27 Mar 2024</td>
                          <td>Business</td>
                          <td class="text-center">
                            <span class="bg-success-focus text-success-main px-24 py-4 rounded-pill fw-medium text-sm">Active</span>
                          </td>
                        </tr>
                        <tr>
                          <td>
                            <div class="d-flex align-items-center">
                              <img src="assets/images/users/user5.png" alt="" class="w-40-px h-40-px rounded-circle flex-shrink-0 me-12 overflow-hidden">
                              <div class="flex-grow-1">
                                <h6 class="text-md mb-0 fw-medium">Arlene McCoy</h6>
                                <span class="text-sm text-secondary-light fw-medium">fellora@mail.ru</span>
                              </div>
                            </div>
                          </td>
                          <td>27 Mar 2024</td>
                          <td>Enterprise </td>
                          <td class="text-center">
                            <span class="bg-success-focus text-success-main px-24 py-4 rounded-pill fw-medium text-sm">Active</span>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
                <div class="tab-pane fade" id="pills-recent-leads" role="tabpanel" aria-labelledby="pills-recent-leads-tab" tabindex="0">
                  <div class="table-responsive scroll-sm">
                    <table class="table bordered-table sm-table mb-0">
                      <thead>
                        <tr>
                          <th scope="col">Users </th>
                          <th scope="col">Registered On</th>
                          <th scope="col">Plan</th>
                          <th scope="col" class="text-center">Status</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr>
                          <td>
                            <div class="d-flex align-items-center">
                              <img src="assets/images/users/user1.png" alt="" class="w-40-px h-40-px rounded-circle flex-shrink-0 me-12 overflow-hidden">
                              <div class="flex-grow-1">
                                <h6 class="text-md mb-0 fw-medium">Dianne Russell</h6>
                                <span class="text-sm text-secondary-light fw-medium">redaniel@gmail.com</span>
                              </div>
                            </div>
                          </td>
                          <td>27 Mar 2024</td>
                          <td>Free</td>
                          <td class="text-center">
                            <span class="bg-success-focus text-success-main px-24 py-4 rounded-pill fw-medium text-sm">Active</span>
                          </td>
                        </tr>
                        <tr>
                          <td>
                            <div class="d-flex align-items-center">
                              <img src="assets/images/users/user2.png" alt="" class="w-40-px h-40-px rounded-circle flex-shrink-0 me-12 overflow-hidden">
                              <div class="flex-grow-1">
                                <h6 class="text-md mb-0 fw-medium">Wade Warren</h6>
                                <span class="text-sm text-secondary-light fw-medium">xterris@gmail.com</span>
                              </div>
                            </div>
                          </td>
                          <td>27 Mar 2024</td>
                          <td>Basic</td>
                          <td class="text-center">
                            <span class="bg-success-focus text-success-main px-24 py-4 rounded-pill fw-medium text-sm">Active</span>
                          </td>
                        </tr>
                        <tr>
                          <td>
                            <div class="d-flex align-items-center">
                              <img src="assets/images/users/user3.png" alt="" class="w-40-px h-40-px rounded-circle flex-shrink-0 me-12 overflow-hidden">
                              <div class="flex-grow-1">
                                <h6 class="text-md mb-0 fw-medium">Albert Flores</h6>
                                <span class="text-sm text-secondary-light fw-medium">seannand@mail.ru</span>
                              </div>
                            </div>
                          </td>
                          <td>27 Mar 2024</td>
                          <td>Standard</td>
                          <td class="text-center">
                            <span class="bg-success-focus text-success-main px-24 py-4 rounded-pill fw-medium text-sm">Active</span>
                          </td>
                        </tr>
                        <tr>
                          <td>
                            <div class="d-flex align-items-center">
                              <img src="assets/images/users/user4.png" alt="" class="w-40-px h-40-px rounded-circle flex-shrink-0 me-12 overflow-hidden">
                              <div class="flex-grow-1">
                                <h6 class="text-md mb-0 fw-medium">Bessie Cooper </h6>
                                <span class="text-sm text-secondary-light fw-medium">igerrin@gmail.com</span>
                              </div>
                            </div>
                          </td>
                          <td>27 Mar 2024</td>
                          <td>Business</td>
                          <td class="text-center">
                            <span class="bg-success-focus text-success-main px-24 py-4 rounded-pill fw-medium text-sm">Active</span>
                          </td>
                        </tr>
                        <tr>
                          <td>
                            <div class="d-flex align-items-center">
                              <img src="assets/images/users/user5.png" alt="" class="w-40-px h-40-px rounded-circle flex-shrink-0 me-12 overflow-hidden">
                              <div class="flex-grow-1">
                                <h6 class="text-md mb-0 fw-medium">Arlene McCoy</h6>
                                <span class="text-sm text-secondary-light fw-medium">fellora@mail.ru</span>
                              </div>
                            </div>
                          </td>
                          <td>27 Mar 2024</td>
                          <td>Enterprise </td>
                          <td class="text-center">
                            <span class="bg-success-focus text-success-main px-24 py-4 rounded-pill fw-medium text-sm">Active</span>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
          </div>
        </div>
      </div>
    </div>

@endsection
