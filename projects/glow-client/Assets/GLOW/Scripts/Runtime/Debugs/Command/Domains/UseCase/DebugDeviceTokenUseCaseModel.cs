namespace GLOW.Debugs.Command.Domains.UseCase
{
    public record DebugDeviceTokenUseCaseModel(string DeviceToken)
    {
        public string DeviceToken { get; } = DeviceToken;
    }
}
