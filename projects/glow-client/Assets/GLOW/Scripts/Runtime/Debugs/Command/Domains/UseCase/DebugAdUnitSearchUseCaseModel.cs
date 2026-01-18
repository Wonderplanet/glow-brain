namespace GLOW.Debugs.Command.Domains.UseCase
{
    public record DebugAdUnitSearchUseCaseModel(string AdUnit, string UniqueId)
    {
        public string AdUnit { get; } = AdUnit;
        public string UniqueId { get; } = UniqueId;
    }
}
