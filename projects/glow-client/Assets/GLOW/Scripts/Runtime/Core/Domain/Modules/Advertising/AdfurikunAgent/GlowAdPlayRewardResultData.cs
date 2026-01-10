namespace GLOW.Core.Modules.Advertising.AdfurikunAgent
{
    public record GlowAdPlayRewardResultData(AdfurikunPlayRewardResultType Type)
    {
        public static GlowAdPlayRewardResultData Empty { get; } =  new(AdfurikunPlayRewardResultType.None);
    }
}
