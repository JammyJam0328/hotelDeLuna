<div x-data>
  <div class="flex items-center justify-between">
    <div class="flex space-x-1">
      <x-native-select wire:model="report_type">
        <option selected>Select Report</option>
        <option value="1">First shift AM 8: AM - 8 PM</option>
        <option value="2">2nd Shift 8 PM-8 AM</option>
      </x-native-select>
      @if ($report_type != null)
        <x-native-select wire:model="floor_id">
          <option selected>Select Floor</option>
          @foreach ($floors as $floor)
            <option value="{{ $floor->id }}">{{ $floor->number }}</option>
          @endforeach
        </x-native-select>
      @endif
    </div>
    <div class="flex space-x-1">

      <x-button @click="printOut($refs.printContainer.outerHTML);" dark label="PRINT" class="font-semibold"
        right-icon="printer" />
    </div>
  </div>
  <div class="border-2 shadow mt-5   border-gray-300 p-5">
    <div x-ref="printContainer" class="">
      <div class="flex items-center space-x-2 text-gray-700">
        <svg class="w-10 h-10" fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
          <!--! Font Awesome Free 6.0.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free (Icons: CC BY 4.0, Fonts: SIL OFL 1.1, Code: MIT License) Copyright 2022 Fonticons, Inc. -->
          <path
            d="M480 0C497.7 0 512 14.33 512 32C512 49.67 497.7 64 480 64V448C497.7 448 512 462.3 512 480C512 497.7 497.7 512 480 512H304V448H208V512H32C14.33 512 0 497.7 0 480C0 462.3 14.33 448 32 448V64C14.33 64 0 49.67 0 32C0 14.33 14.33 0 32 0H480zM112 96C103.2 96 96 103.2 96 112V144C96 152.8 103.2 160 112 160H144C152.8 160 160 152.8 160 144V112C160 103.2 152.8 96 144 96H112zM224 144C224 152.8 231.2 160 240 160H272C280.8 160 288 152.8 288 144V112C288 103.2 280.8 96 272 96H240C231.2 96 224 103.2 224 112V144zM368 96C359.2 96 352 103.2 352 112V144C352 152.8 359.2 160 368 160H400C408.8 160 416 152.8 416 144V112C416 103.2 408.8 96 400 96H368zM96 240C96 248.8 103.2 256 112 256H144C152.8 256 160 248.8 160 240V208C160 199.2 152.8 192 144 192H112C103.2 192 96 199.2 96 208V240zM240 192C231.2 192 224 199.2 224 208V240C224 248.8 231.2 256 240 256H272C280.8 256 288 248.8 288 240V208C288 199.2 280.8 192 272 192H240zM352 240C352 248.8 359.2 256 368 256H400C408.8 256 416 248.8 416 240V208C416 199.2 408.8 192 400 192H368C359.2 192 352 199.2 352 208V240zM256 288C211.2 288 173.5 318.7 162.1 360.2C159.7 373.1 170.7 384 184 384H328C341.3 384 352.3 373.1 349 360.2C338.5 318.7 300.8 288 256 288z">
          </path>
        </svg>
        <div class="">
          <h1 class="text-2xl leading-5 border-b border-gray-700 font-bold">HIMS</h1>
          <h1 class="text-xs font-semibold">{{ auth()->user()->branch->name }}</h1>
        </div>
      </div>
      <div class="mt-10 text-center text-2xl text-gray-700 font-bold">
        <h1>SALES REPORT</h1>
      </div>
      <table id="example" class="table-auto mt-5" style="width:100%">
        <thead class="font-normal">
          <tr>
            <th class="border border-gray-600 text-left px-2 text-sm font-semibold text-gray-700 py-2">FLOORS</th>
            <th class="border border-gray-600 text-left px-2 text-sm font-semibold text-gray-700 py-2">AMOUNT</th>
          </tr>
        </thead>
        <tbody class="">

          @foreach ($transactions as $transaction)
            <tr>
              <td class="border border-gray-700 px-3 py-1">{{ $transaction->room->floor->number }} FLOOR</td>
              <td class="border border-gray-700 px-3 py-1">&#8369;{{ number_format($transaction->payable_amount, 2) }}
              </td>
            </tr>
          @endforeach
          <tr class="col-span-2">
            <td class=" px-3 py-1 font-bold text-right text-gray-700">TOTAL:</td>
            </td>
            <td class="border border-gray-600 px-3 py-1 font-bold text-gray-700">
              &#8369;{{ number_format($transactions->sum('payable_amount'), 2) }}</td>
          </tr>
        </tbody>
      </table>
      <div class="mt-20">
        <div class="flex flex-col space-y-7">
          <div class="text-gray-700">
            <h1 class="text-sm font-semibold">Prepared By:</h1>
            <h1 class="text-sm mt-8 w-48 border-b border-gray-400"></h1>
          </div>
          <div class="text-gray-700">
            <h1 class="text-sm font-semibold">Verified By:</h1>
            <h1 class="text-sm mt-8 w-48 border-b border-gray-400"></h1>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    function printOut(data) {
      var mywindow = window.open('', 'Sales Report', 'height=1000,width=1000');
      mywindow.document.write('<html><head>');
      mywindow.document.write('<title>Sales Report</title>');
      mywindow.document.write(`<link rel="stylesheet" href="{{ Vite::asset('resources/css/app.css') }}" />`);
      mywindow.document.write('</head><body >');
      mywindow.document.write(data);
      mywindow.document.write('</body></html>');

      mywindow.document.close();
      mywindow.focus();
      setTimeout(() => {
        mywindow.print();
        return true;
      }, 1000);


    }
  </script>
</div>
