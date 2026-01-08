using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.Home.Presentation.ViewModels
{
    public record HomeHeaderViewModel(
        UserName UserName,
        UserAvatarPath UserAvatarPath,
        UserAvatarFramePath UserAvatarFramePath,
        EmblemIconAssetPath EmblemIconAssetPath,
        UserLevel Level,
        RelativeUserExp Exp,
        RelativeUserExp NextExp,
        Stamina Stamina,
        Stamina MaxStamina,
        Coin Coin,
        FreeDiamond FreeDiamond,
        PaidDiamond PaidDiamond);

    public record UserAvatarFramePath(string Value);
}
