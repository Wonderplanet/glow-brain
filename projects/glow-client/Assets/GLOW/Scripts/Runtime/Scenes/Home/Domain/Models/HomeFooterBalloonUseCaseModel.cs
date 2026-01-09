namespace GLOW.Scenes.Home.Domain.Models
{
    public record HomeFooterBalloonUseCaseModel(
        HomeFooterBalloonShownFlag IsShowGachaBanner,
        HomeFooterBalloonShownFlag IsOpeningAdventBattle,
        HomeFooterBalloonShownFlag IsOpeningPvp)
    {
        public static HomeFooterBalloonUseCaseModel Empty { get; } = new(
            HomeFooterBalloonShownFlag.Empty,
            HomeFooterBalloonShownFlag.Empty,
            HomeFooterBalloonShownFlag.Empty);
    };
}
