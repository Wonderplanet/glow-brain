using System;
using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Scenes.PassShop.Presentation.ViewModel;

namespace GLOW.Scenes.GachaContent.Presentation.ViewModels
{
    public record GachaContentViewModel(
        MasterDataId GachaId,
        DrawableFlag IsDrawableFlagByHasDrawLimitedCount, // 回数上限に到達しているか
        GachaName GachaName,
        GachaType GachaType,
        DateTimeOffset EndAt,
        IsDisplayGachaDrawButton IsDisplayGachaAdDrawButton, // 広告ガシャボタンの表示
        AdGachaDrawableFlag CanAdGachaDraw, // 広告ガシャを引けるかどうか
        AdGachaResetRemainingText AdGachaResetRemainingText, // 広告の残り時間テキスト
        AdGachaDrawableCount AdGachaDrawableCount, // 広告ガシャの引ける回数
        GachaRemainingTimeText GachaRemainingTimeText, // ガシャの残り時間
        GachaThresholdText GachaThresholdText, // ガシャの天井
        GachaDescription GachaDescription, // ガシャの説明
        CostType SingleDrawCostType, // 単発のコストタイプ
        GachaDrawLimitCount SingleDrawLimitCount, // 単発のガシャの回数上限
        IsDisplayGachaDrawButton IsDisplaySingleDrawButton, // 単発のガシャボタンを表示しない場合
        DrawableFlag IsEnoughSingleDrawCostItem, // 単発のコストのチケットが足りているか
        PlayerResourceIconAssetPath SingleDrawCostIconAssetPath, // 単発のコストアイコンアセットパス
        CostAmount SingleDrawCostAmount, // 単発のコスト
        CostType MultiDrawCostType, // 10連のコストタイプ
        GachaDrawLimitCount MultiDrawLimitCount, // 10連のガシャの回数上限
        IsDisplayGachaDrawButton IsDisplayMultiDrawButton, // 10連のガシャボタンを表示しない場合
        DrawableFlag IsEnoughMultiDrawCostItem, // 10連のコストのチケットが足りているか
        PlayerResourceIconAssetPath MultiDrawCostIconAssetPath, // 10連のコストアイコンアセットパス
        CostAmount MultiDrawCostAmount, // 10連のコスト
        // 排出ユニット情報
        List<GachaDisplayUnitViewModel> GachaDisplayUnitViewModels,
        HeldAdSkipPassInfoViewModel HeldAdSkipPassInfoViewModel,
        GachaFixedPrizeDescription GachaFixedPrizeDescription, // 10連ガシャの確定枠テキスト
        GachaUnlockConditionType GachaUnlockConditionType,
        GachaLogoAssetPath GachaLogoAssetPath
    )
    {
        public bool IsSingleDrawButtonEnabled()
        {
            // 単発ガシャボタンが押せる状態か（コストが足りていて、かつ回数上限に到達していない場合true）
            // ※コストがダイヤの場合は、ボタン押下でダイヤ購入誘導を行うためtrueを返す（実際に引けるかどうかは別）
            return IsEnoughSingleDrawCostItem && IsDrawableFlagByHasDrawLimitedCount;
        }

        public bool ShouldShowSingleDrawButtonGrayOut()
        {
            // アイテムコスト不足or回数上限到達時、単発ガシャボタンをグレーアウト
            return !IsEnoughSingleDrawCostItem || !IsDrawableFlagByHasDrawLimitedCount;
        }

        public bool ShouldShowLackOfSingleItemText()
        {
            // 上限は到達していないが素材不足なら不足テキスト表示
            return !IsEnoughSingleDrawCostItem && IsDrawableFlagByHasDrawLimitedCount;
        }

        public bool ShouldShowSingleDrawButtonInfo()
        {
            // チケットがコスト or 回数制限時、単発ガシャボタンのN回テキストを非表示にする
            return SingleDrawCostType != CostType.Item || !IsDrawableFlagByHasDrawLimitedCount;
        }

        public bool ShouldShowSingleDrawTicketCostText()
        {
            // チケットがコストかつ引ける場合、チケットのコストを表示する
            return SingleDrawCostType == CostType.Item && IsEnoughSingleDrawCostItem && IsDrawableFlagByHasDrawLimitedCount;
        }

        public bool ShouldShowSingleGachaResources()
        {
            // 回数上限の場合、単発コスト表示を消す
            return IsDrawableFlagByHasDrawLimitedCount;
        }

        public bool ShouldShowSingleLimitedCountText()
        {
            // 利用可能回数表示
            return SingleDrawLimitCount != GachaDrawLimitCount.Unlimited;
        }

        public bool IsMultiDrawButtonEnabled()
        {
            // 10連ガシャボタンが押せる状態か（コストが足りていて、かつ回数上限に到達していない場合true）
            // ※コストがダイヤの場合は、ボタン押下でダイヤ購入誘導を行うためtrueを返す（実際に引けるかどうかは別）
            return IsEnoughMultiDrawCostItem && IsDrawableFlagByHasDrawLimitedCount;
        }

        public bool ShouldShowMultiDrawButtonGrayOut()
        {
            // チケットがコストかつ引けない場合、チケット不足時テキストを表示
            return !IsEnoughMultiDrawCostItem || !IsDrawableFlagByHasDrawLimitedCount;
        }

        public bool ShouldShowLackOfMultiItemText()
        {
            return !IsEnoughMultiDrawCostItem && IsDrawableFlagByHasDrawLimitedCount;
        }

        public bool ShouldShowMultiDrawButtonInfo()
        {
            // チケットがコスト or 回数制限時、10連ガシャボタンのN回テキストを非表示にする
            return MultiDrawCostType != CostType.Item && IsDrawableFlagByHasDrawLimitedCount;
        }

        public bool ShouldShowMultiDrawTicketCostText()
        {
            // チケットがコストかつ引ける場合、チケットのコストを表示する
            return MultiDrawCostType == CostType.Item && IsEnoughMultiDrawCostItem && IsDrawableFlagByHasDrawLimitedCount;
        }

        public bool ShouldShowMultiGachaResources()
        {
            // 回数上限到達した場合、10連コスト表示を消す
            return IsDrawableFlagByHasDrawLimitedCount;
        }

        public bool ShouldShowMultiLimitedCountText()
        {
            // 利用可能回数表示
            return MultiDrawLimitCount != GachaDrawLimitCount.Unlimited;
        }

        public bool ShouldShowSingleDrawButtonCostArea()
        {
            // 単発ガシャボタンのコスト表示エリアを表示するか（ボタン自体が表示され、かつ回数上限未到達の場合）
            return IsDisplaySingleDrawButton && IsDrawableFlagByHasDrawLimitedCount;
        }

        public bool ShouldShowMultiDrawButtonCostArea()
        {
            // 10連ガシャボタンのコスト表示エリアを表示するか（ボタン自体が表示され、かつ回数上限未到達の場合）
            return IsDisplayMultiDrawButton && IsDrawableFlagByHasDrawLimitedCount;
        }
    }
}
