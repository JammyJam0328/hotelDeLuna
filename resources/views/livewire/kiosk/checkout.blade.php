<div x-data="{ scan: @entangle('scannerpanel') }" class="font-rubik">
  <div class="mx-40 flex mt-5 h-[32rem]">

    <div class="relative w-full">
      <h1 class="text-2xl font-black text-white">CHECK-OUT DETAILS |</h1>

      <div class="mt-5">
        @if ($guest == null)
          none
        @else
          <div class="mt-2  w-full flex space-x-3">
            <div class="flex-1 flex flex-col">
              <div class="flex-1">
                <dl class="px-10 pt-6 space-y-3 text-sm font-medium text-gray-500 border-t border-gray-200">
                  @forelse ($transactions as $transaction)
                    @switch($transaction->transaction_type_id)
                      @case(1)
                        <div class="flex justify-between">
                          <dt class="">
                            <div class="flex flex-col">
                              <div class="font-bold text-white uppercase border-b">
                                {{ $transaction->check_in_detail->rate->type->name }}</div>
                              <div class="text-gray-300"> RM
                                #{{ $transaction->check_in_detail->room->number }} |
                                {{ ordinal($transaction->check_in_detail->room->floor->number) }} FLOOR
                              </div>
                            </div>
                          </dt>
                          <dd class="text-lg font-bold text-white">
                            &#8369;{{ number_format($transaction->payable_amount, 2) }}
                          </dd>
                        </div>
                      @break

                      @case(3)
                        <div class="flex justify-between pt-7">
                          <dt class="">
                            <div class="flex flex-col">
                              <div class="font-bold text-white uppercase border-b">KITCHEN ORDERS</div>
                              <div class="text-gray-300">{{ $transaction->orders->count() }} Meals</div>
                            </div>
                          </dt>
                          <dd class="text-lg font-bold text-white">
                            &#8369;{{ number_format($transaction->payable_amount, 2) }}
                          </dd>
                        </div>
                      @break

                      @default
                    @endswitch
                    @empty
                    @endforelse



                  </dl>
                  <div class="flex justify-between px-10 pt-7">
                    <dt class="">
                      <div class="flex items-center space-x-2">
                        <div class="flex flex-col">
                          <div class="font-bold text-white uppercase border-b">CHECK-IN DEPOSITS</div>
                          <div class="text-gray-300">Room Key & TV Remote </div>
                        </div>
                        @if ($transactions->where('transaction_type_id', 2)->count() > 1)
                          <div class="text-white"> x
                            {{ $transactions->where('transaction_type_id', 2)->count() }}</div>
                        @endif
                        <div class="text-white"></div>
                      </div>
                    </dt>
                    <dd class="text-lg font-bold text-white">
                      &#8369;{{ number_format($transactions->where('transaction_type_id', 2)->sum('payable_amount'), 2) }}
                    </dd>
                  </div>
                </div>
                <div class=" left-0 w-full border-b bottom-5">
                  <div class="flex items-center justify-between w-full px-10">
                    <dt class="">
                      <div class="flex items-center space-x-2">
                        <h1 class="text-2xl font-bold text-white">TOTAL</h1>
                      </div>
                    </dt>
                    <dd class="text-2xl font-bold text-white">
                      &#8369;{{ number_format($transactions->sum('payable_amount'), 2) }}
                    </dd>
                  </div>
                </div>
              </div>
              <div class="p-5 py-8 bg-white shadow-2xl bg-opacity-30 w-96 rounded-3xl">
                <div class="grid place-content-center">
                  <img
                    src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ $guests['qr_code'] ?? null }}"
                    alt="">
                </div>
                <div class="flex-none p-6 text-center">
                  <h2 class="mt-3 font-bold text-white uppercase">{{ $guests['name'] ?? '' }}</h2>
                </div>
                <div class="flex flex-col justify-between flex-auto p-6">
                  <dl class="grid grid-cols-1 text-sm text-white gap-x-6 gap-y-3">
                    <dt class="col-end-1 font-semibold text-gray-900">Phone</dt>
                    <dd>{{ $guests['contact_number'] ?? '***********' }}</dd>
                    <dt class="col-end-1 font-semibold text-gray-900">QR_CODE</dt>
                    <dd class="truncate"><a href="https://example.com"
                        class="text-white ">{{ $guests['qr_code'] ?? '***********' }}</a></dd>

                  </dl>

                  <div class="flex justify-center w-full mt-20">
                    <a href="{{ route('kiosk.transaction') }}"
                      class="w-full px-4 py-2 text-xl font-bold text-center text-gray-700 bg-white rounded-full">
                      OK
                    </a>
                  </div>
                </div>
              </div>
            </div>
          @endif
        </div>
      </div>

      {{-- <div class="relative flex-1">
            <h1 class="text-2xl font-black text-white">CHECK-OUT DETAILS |</h1>
            @if ($guest != null)
                <div class="mt-2 div">

                    <dl class="px-10 pt-6 space-y-3 text-sm font-medium text-gray-500 border-t border-gray-200">
                        @forelse ($transactions as $transaction)
                            @switch($transaction->transaction_type_id)
                                @case(1)
                                    <div class="flex justify-between">
                                        <dt class="">
                                            <div class="flex flex-col">
                                                <div class="font-bold text-white uppercase border-b">
                                                    {{ $transaction->check_in_detail->rate->type->name }}</div>
                                                <div class="text-gray-300"> RM
                                                    #{{ $transaction->check_in_detail->room->number }} |
                                                    {{ ordinal($transaction->check_in_detail->room->floor->number) }} FLOOR
                                                </div>
                                            </div>
                                        </dt>
                                        <dd class="text-lg font-bold text-white">
                                            &#8369;{{ number_format($transaction->payable_amount, 2) }}
                                        </dd>
                                    </div>
                                @break

                                @case(3)
                                    <div class="flex justify-between pt-7">
                                        <dt class="">
                                            <div class="flex flex-col">
                                                <div class="font-bold text-white uppercase border-b">KITCHEN ORDERS</div>
                                                <div class="text-gray-300">{{ $transaction->orders->count() }} Meals</div>
                                            </div>
                                        </dt>
                                        <dd class="text-lg font-bold text-white">
                                            &#8369;{{ number_format($transaction->payable_amount, 2) }}
                                        </dd>
                                    </div>
                                @break

                                @default
                            @endswitch
                            @empty
                            @endforelse



                        </dl>
                        <div class="flex justify-between px-10 pt-7">
                            <dt class="">
                                <div class="flex items-center space-x-2">
                                    <div class="flex flex-col">
                                        <div class="font-bold text-white uppercase border-b">CHECK-IN DEPOSITS</div>
                                        <div class="text-gray-300">Room Key & TV Remote </div>
                                    </div>
                                    @if ($transactions->where('transaction_type_id', 2)->count() > 1)
                                        <div class="text-white"> x
                                            {{ $transactions->where('transaction_type_id', 2)->count() }}</div>
                                    @endif
                                    <div class="text-white"></div>
                                </div>
                            </dt>
                            <dd class="text-lg font-bold text-white">
                                &#8369;{{ number_format($transactions->where('transaction_type_id', 2)->sum('payable_amount'), 2) }}
                            </dd>
                        </div>
                        <div class="absolute left-0 w-full border-b bottom-5">
                            <div class="flex items-center justify-between w-full px-10">
                                <dt class="">
                                    <div class="flex items-center space-x-2">
                                        <h1 class="text-2xl font-bold text-white">TOTAL</h1>
                                    </div>
                                </dt>
                                <dd class="text-2xl font-bold text-white">
                                    &#8369;{{ number_format($transactions->sum('payable_amount'), 2) }}
                                </dd>
                            </div>
                        </div>
                        <div class="p-5 py-8 bg-white shadow-2xl bg-opacity-30 w-96 rounded-3xl">
                            <div class="grid place-content-center">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=jdjshjshdjshd"
                                    alt="">
                            </div>
                            <div class="flex-none p-6 text-center">
                                <h2 class="mt-3 font-bold text-white uppercase">{{ $guests['name'] ?? '' }}</h2>
                            </div>
                            <div class="flex flex-col justify-between flex-auto p-6">
                                <dl class="grid grid-cols-1 text-sm text-white gap-x-6 gap-y-3">
                                    <dt class="col-end-1 font-semibold text-gray-900">Phone</dt>
                                    <dd>{{ $guests['contact_number'] ?? '***********' }}</dd>
                                    <dt class="col-end-1 font-semibold text-gray-900">QR_CODE</dt>
                                    <dd class="truncate"><a href="https://example.com"
                                            class="text-white ">{{ $guests['qr_code'] ?? '***********' }}</a></dd>

                            </div>
                @endif
            </div>
            <div class="p-5 py-8 bg-white shadow-2xl bg-opacity-30 w-96 rounded-3xl">
                <div class="grid place-content-center">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ $guests['qr_code'] ?? null }}"
                        alt="">
                </div>
                <div class="flex-none p-6 text-center">
                    <h2 class="mt-3 font-bold text-white uppercase">{{ $guests['name'] ?? '' }}</h2>
                </div>
                <div class="flex flex-col justify-between flex-auto p-6">
                    <dl class="grid grid-cols-1 text-sm text-white gap-x-6 gap-y-3">
                        <dt class="col-end-1 font-semibold text-gray-900">Phone</dt>
                        <dd>{{ $guests['contact_number'] ?? '***********' }}</dd>
                        <dt class="col-end-1 font-semibold text-gray-900">QR_CODE</dt>
                        <dd class="truncate"><a href="https://example.com"
                                class="text-white ">{{ $guests['qr_code'] ?? '***********' }}</a></dd>

                    </dl>

                    <div class="flex justify-center w-full mt-20">
                        <a href="{{ route('kiosk.transaction') }}"
                            class="w-full px-4 py-2 text-xl font-bold text-center text-gray-700 bg-white rounded-full">
                            OK
                        </a>
                    </div>
                </div>
            </div>
        </div> --}}
      <div x-show="scan" x-cloak class="relative z-10" role="dialog" aria-modal="true"> -->
        <div x-show="scan" x-cloak x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
          x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
          x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
          class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-90"></div>

        <div class="fixed inset-0 z-10 p-4 overflow-y-auto sm:p-6 md:p-20">
          <div x-show="scan" x-cloak x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="mx-auto max-w-2xl h-[35rem]  relative transform divide-y divide-gray-100 overflow-hidden transition-all">
            <div class="relative h-full">

              <div class="absolute top-0 bottom-0 left-0 right-0 flex items-center justify-center">
                <div class="flex flex-col items-center justify-center w-full mx-10 bg-opacity-70 rounded-2xl">
                  <input type="number" autofocus wire:model="scanner" placeholder="ENTER QR NUMBER"
                    class="w-full h-20 text-3xl font-bold text-center text-gray-600 bg-gray-100 shadow-xl rounded-2xl">
                  <div class="mt-5">
                    <x-button label="SEARCH" wire:click="searchCode" dark lg class="font-bold" />
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="grid mt-2 place-content-center">
            <x-button href="{{ route('kiosk.transaction') }}" md icon="arrow-circle-left" label="BACK" white
              class="font-semibold" />
          </div>

        </div>
      </div>

    </div>
