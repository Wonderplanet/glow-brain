using System;
using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Scenes.PassShop.Domain.Model;

namespace GLOW.Scenes.GachaContent.Domain.Model
{
    public record GachaContentUseCaseModel(
        MasterDataId GachaId,
        DrawableFlag DrawableFlagByHasDrawLimitedCount,      // 回数上限に到達しているか
        GachaName GachaName,
        GachaType GachaType,
        DateTimeOffset EndAt,
        IsDisplayGachaDrawButton IsDisplayAdGachaDrawButton,  // 広告ガチャボタンの表示
        AdGachaDrawableFlag CanAdGachaDraw,  // 広告ガチャを引けるかどうか
        AdGachaResetRemainingText AdGachaResetRemainingText,        // 広告の残り時間テキスト
        AdGachaDrawableCount AdGachaDrawableCount,      // 広告ガチャの引ける回数
        GachaRemainingTimeText GachaRemainingTimeText,  // ガチャの残り時間
        GachaThresholdText GachaThresholdText,          // ガチャの天井
        GachaDescription GachaDescription,              // ガチャの説明
        CostType SingleDrawCostType,                    // 単発のコストタイプ
        GachaDrawLimitCount SingleDrawLimitCount,        // 単発のガチャの回数上限
        IsDisplayGachaDrawButton IsDisplaySingleDrawButton,                 // 単発のガチャボタンを表示しない場合
        DrawableFlag IsEnoughSingleDrawCostItem,                             // 単発のコストのチケットが足りているか
        PlayerResourceIconAssetPath SingleDrawCostIconAssetPath,            // 単発のコストアイコンアセットパス
        CostAmount SingleDrawCostAmount,                                    // 単発のコスト
        CostType MultiDrawCostType,                                         // 10連のコストタイプ
        GachaDrawLimitCount MultiDrawLimitCount,                           // 10連のガチャの回数上限
        IsDisplayGachaDrawButton IsDisplayMultiDrawButton,                    // 10連のガチャボタンを表示しない場合
        DrawableFlag IsEnoughMultiDrawCostItem,                              // 10連のコストのチケットが足りているか
        PlayerResourceIconAssetPath MultiDrawCostIconAssetPath,             // 10連のコストアイコンアセットパス
        CostAmount MultiDrawCostAmount,                                     // 10連のコスト
        // 排出ユニット情報
        IReadOnlyList<GachaDisplayUnitModel> GachaPickUpUnitModels,
        HeldAdSkipPassInfoModel HeldAdSkipPassInfoModel,
        GachaFixedPrizeDescription GachaFixedPrizeDescription,  // 10連ガチャの確定枠テキスト
        GachaUnlockConditionType GachaUnlockConditionType,
        GachaLogoAssetPath GachaLogoAssetPath)
    {
        public static GachaContentUseCaseModel Empty { get; } = new (
            MasterDataId.Empty,
            DrawableFlag.False,
            GachaName.Empty,
            GachaType.Normal,
            DateTimeOffset.MinValue,
            IsDisplayGachaDrawButton.False,
            AdGachaDrawableFlag.False,
            AdGachaResetRemainingText.Empty,
            AdGachaDrawableCount.Zero,
            GachaRemainingTimeText.Empty,
            GachaThresholdText.Empty,
            GachaDescription.Empty,
            CostType.Free,
            GachaDrawLimitCount.Zero,
            IsDisplayGachaDrawButton.False,
            DrawableFlag.False,
            PlayerResourceIconAssetPath.Empty,
            CostAmount.Zero,
            CostType.Free,
            GachaDrawLimitCount.Zero,
            IsDisplayGachaDrawButton.False,
            DrawableFlag.False,
            PlayerResourceIconAssetPath.Empty,
            CostAmount.Zero,
            new List<GachaDisplayUnitModel>(),
            HeldAdSkipPassInfoModel.Empty,
            GachaFixedPrizeDescription.Empty,
            GachaUnlockConditionType.None,
            GachaLogoAssetPath.Empty);

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
