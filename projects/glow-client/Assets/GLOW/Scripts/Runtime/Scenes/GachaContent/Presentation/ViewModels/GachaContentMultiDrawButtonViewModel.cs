using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Scenes.GachaContent.Presentation.ViewModels
{
    public record GachaContentMultiDrawButtonViewModel(
        CostType MultiDrawCostType, // 10連のコストタイプ
        GachaDrawLimitCount MultiDrawLimitCount, // 10連のガシャの回数上限
        IsDisplayGachaDrawButton IsDisplayMultiDrawButton, // 10連のガシャボタンを表示しない場合
        DrawableFlag IsEnoughMultiDrawCostItem, // 10連のコストのチケットが足りているか
        PlayerResourceIconAssetPath MultiDrawCostIconAssetPath, // 10連のコストアイコンアセットパス
        CostAmount MultiDrawCostAmount, // 10連のコスト
        GachaFixedPrizeDescription GachaFixedPrizeDescription// 10連ガシャの確定枠テキスト
    );
}