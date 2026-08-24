<table>

    <thead>

        <tr>

            <th>Request No.</th>

            <th>Ministry</th>

            <th>Department</th>

            <th>Category</th>

            <th>Problem</th>

            <th>Requested By</th>

            <th>Technical Officer</th>

            <th>Status</th>

            <th>Submitted Date</th>

        </tr>

    </thead>


    <tbody>

        @foreach($requests as $request)

            <tr>

                <td>
                    {{ $request->request_number }}
                </td>


                <td>
                    {{ $request->department->ministry_name ?? '-' }}
                </td>


                <td>
                    {{ $request->department->name ?? '-' }}
                </td>


                <td>
                    {{ $request->category->name ?? '-' }}
                </td>


                <td>
                    {{ $request->title }}
                </td>


                <td>
                    {{ $request->requestedBy->name ?? '-' }}
                </td>


                <td>
                    {{ $request->assignedTo->name ?? 'Not Assigned' }}
                </td>


                <td>
                    {{ $request->status }}
                </td>


                <td>
                    {{ $request->created_at->format('d/m/Y H:i') }}
                </td>

            </tr>

        @endforeach

    </tbody>

</table>