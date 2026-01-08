namespace WPFramework.Modules.TimeCalibration
{
    public sealed class GoogleNtpContext : INtpContext
    {
        public string Domain { get; } = "time.google.com";
        public int Port { get; } = 123;
    }
}
