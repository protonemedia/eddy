<div class="relative flex flex-col items-center justify-center bg-gradient-to-b from-gray-800 to-gray-900 px-8 py-24 selection:bg-red-500 selection:text-white">
    <div class="flex max-w-5xl grid-cols-3 flex-col items-center gap-16 md:grid">
        <div class="prose-xl col-span-2 text-gray-100">
            <h2 class="text-green-400">Database and File Backups</h2>

            <p>
                Eddy Server Management ensures automatic backups of your databases and files, supporting
                <b class="text-white">S3, FTP, and SFTP.</b>
                Customize backup schedules and retention policies to fit your requirements. Receive timely notifications for completed or failed backups. Say
                goodbye to data loss.
            </p>
        </div>

        <img
            src="{{ asset('features/backups.png') }}"
            alt="Database and File Backups at Eddy Server Management"
            class="rounded shadow-md sm:w-1/2 md:w-full"
        />
    </div>
</div>
