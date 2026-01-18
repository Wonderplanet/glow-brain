using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;

namespace GLOW.Scenes.GachaContent.Presentation.ViewModels
{
    public record GachaContentSingleDrawButtonViewModel(
        CostType SingleDrawCostType, // 単発のコストタイプ
        GachaDrawLimitCount SingleDrawLimitCount, // 単発のガシャの回数上限
        IsDisplayGachaDrawButton IsDisplaySingleDrawButton, // 単発のガシャボタンを表示しない場合
        DrawableFlag IsEnoughSingleDrawCostItem, // 単発のコストのチケットが足りているか
        PlayerResourceIconAssetPath SingleDrawCostIconAssetPath, // 単発のコストアイコンアセットパス
        CostAmount SingleDrawCostAmount // 単発のコスト
    );
}