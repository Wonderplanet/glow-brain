using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Scenes.PassShop.Presentation.ViewModel;

namespace GLOW.Scenes.GachaContent.Presentation.ViewModels
{
    public record GachaContentAdDrawButtonViewModel(
        IsDisplayGachaDrawButton IsDisplayAdGachaDrawButton, // 広告ガシャボタンの表示
        AdGachaDrawableFlag CanAdGachaDraw, // 広告ガシャを引けるかどうか
        AdGachaResetRemainingText AdGachaResetRemainingText, // 広告の残り時間テキスト
        AdGachaDrawableCount AdGachaDrawableCount, // 広告ガシャの引ける回数
        HeldAdSkipPassInfoViewModel HeldAdSkipPassInfoViewModel
    );
}