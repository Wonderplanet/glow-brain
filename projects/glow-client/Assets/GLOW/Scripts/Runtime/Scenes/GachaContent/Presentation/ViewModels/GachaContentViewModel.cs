using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Scenes.GachaContent.Domain.Model;
using GLOW.Scenes.GachaList.Presentation.ViewModels;
using GLOW.Scenes.PassShop.Presentation.ViewModel;

namespace GLOW.Scenes.GachaContent.Presentation.ViewModels
{
    public record GachaContentViewModel(
        MasterDataId OprGachaId,
        DrawableFlag IsDrawableFlagByHasDrawLimitedCount, // 回数上限に到達しているか
        GachaName GachaName,
        GachaType GachaType,
        DateTimeOffset EndAt,
        GachaContentDetailButtonFlag GachaContentDetailButtonFlag, //お知らせ表示するか
        GachaRemainingTimeText GachaRemainingTimeText, // ガシャの残り時間
        GachaThresholdText GachaThresholdText, // ガシャの天井
        GachaDescription GachaDescription, // ガシャの説明
        GachaContentSingleDrawButtonViewModel SingleDrawButtonViewModel, // 単発ガシャボタン情報
        GachaContentMultiDrawButtonViewModel MultiDrawButtonViewModel, // 10連ガシャボタン情報
        GachaContentAdDrawButtonViewModel AdDrawButtonViewModel, // 広告ガシャボタン情報
        GachaUnlockConditionType GachaUnlockConditionType,
        GachaLogoAssetPath GachaLogoAssetPath,
        IReadOnlyList<MasterDataId> PickupMstUnitIds)
    {
        public static GachaContentViewModel Empty { get; } =
            new GachaContentViewModel(
                MasterDataId.Empty,
                new DrawableFlag(false),
                GachaName.Empty,
                GachaType.Normal,
                DateTimeOffset.MinValue,
                GachaContentDetailButtonFlag.False,
                GachaRemainingTimeText.Empty,
                GachaThresholdText.Empty,
                GachaDescription.Empty,
                new GachaContentSingleDrawButtonViewModel(
                    CostType.Diamond,
                    GachaDrawLimitCount.Unlimited,
                    IsDisplayGachaDrawButton.False,
                    new DrawableFlag(false),
                    PlayerResourceIconAssetPath.Empty,
                    CostAmount.Empty),
                new GachaContentMultiDrawButtonViewModel(
                    CostType.Diamond,
                    GachaDrawLimitCount.Unlimited,
                    IsDisplayGachaDrawButton.False,
                    new DrawableFlag(false),
                    PlayerResourceIconAssetPath.Empty,
                    CostAmount.Empty,
                    GachaFixedPrizeDescription.Empty),
                new GachaContentAdDrawButtonViewModel(
                    IsDisplayGachaDrawButton.False,
                    AdGachaDrawableFlag.Empty,
                    AdGachaResetRemainingText.Empty,
                    AdGachaDrawableCount.Zero,
                    HeldAdSkipPassInfoViewModel.Empty),
                GachaUnlockConditionType.None,
                GachaLogoAssetPath.Empty,
                new List<MasterDataId>()
            );
        public bool IsSingleDrawButtonEnabled()
        {
            // 単発ガシャボタンが押せる状態か（コストが足りていて、かつ回数上限に到達していない場合true）
            // ※コストがダイヤの場合は、ボタン押下でダイヤ購入誘導を行うためtrueを返す（実際に引けるかどうかは別）
            return SingleDrawButtonViewModel.IsEnoughSingleDrawCostItem && IsDrawableFlagByHasDrawLimitedCount;
        }

        public bool ShouldShowSingleDrawButtonGrayOut()
        {
            // アイテムコスト不足or回数上限到達時、単発ガシャボタンをグレーアウト
            return !SingleDrawButtonViewModel.IsEnoughSingleDrawCostItem || !IsDrawableFlagByHasDrawLimitedCount;
        }

        public bool ShouldShowLackOfSingleItemText()
        {
            // 上限は到達していないが素材不足なら不足テキスト表示
            return !SingleDrawButtonViewModel.IsEnoughSingleDrawCostItem && IsDrawableFlagByHasDrawLimitedCount;
        }

        public string GetLackItemText()
        {
            return GachaType == GachaType.Medal
                ? "アイテム不足"
                : "チケット不足";
        }

        public bool ShouldShowSingleDrawButtonInfo()
        {
            // チケットがコスト or 回数制限時、単発ガシャボタンのN回テキストを非表示にする
            return SingleDrawButtonViewModel.SingleDrawCostType != CostType.Item || !IsDrawableFlagByHasDrawLimitedCount;
        }

        public bool ShouldShowSingleDrawTicketCostText()
        {
            // チケットがコストかつ引ける場合、チケットのコストを表示する
            return SingleDrawButtonViewModel.SingleDrawCostType == CostType.Item &&
                   SingleDrawButtonViewModel.IsEnoughSingleDrawCostItem &&
                   IsDrawableFlagByHasDrawLimitedCount;
        }

        public string GetItemDrawCostText()
        {
            return GachaType == GachaType.Medal
                ? "アイテムで引く"
                : "チケットで引く";
        }

        public bool ShouldShowSingleGachaResources()
        {
            // 回数上限の場合、単発コスト表示を消す
            return IsDrawableFlagByHasDrawLimitedCount;
        }

        public bool ShouldShowSingleLimitedCountText()
        {
            // 利用可能回数表示
            return SingleDrawButtonViewModel.SingleDrawLimitCount != GachaDrawLimitCount.Unlimited;
        }

        public bool IsMultiDrawButtonEnabled()
        {
            // 10連ガシャボタンが押せる状態か（コストが足りていて、かつ回数上限に到達していない場合true）
            // ※コストがダイヤの場合は、ボタン押下でダイヤ購入誘導を行うためtrueを返す（実際に引けるかどうかは別）
            return MultiDrawButtonViewModel.IsEnoughMultiDrawCostItem && IsDrawableFlagByHasDrawLimitedCount;
        }

        public bool ShouldShowMultiDrawButtonGrayOut()
        {
            // チケットがコストかつ引けない場合、チケット不足時テキストを表示
            return !MultiDrawButtonViewModel.IsEnoughMultiDrawCostItem || !IsDrawableFlagByHasDrawLimitedCount;
        }

        public bool ShouldShowLackOfMultiItemText()
        {
            return !MultiDrawButtonViewModel.IsEnoughMultiDrawCostItem && IsDrawableFlagByHasDrawLimitedCount;
        }

        public bool ShouldShowMultiDrawButtonInfo()
        {
            // チケットがコスト or 回数制限時、10連ガシャボタンのN回テキストを非表示にする
            return MultiDrawButtonViewModel.MultiDrawCostType != CostType.Item && IsDrawableFlagByHasDrawLimitedCount;
        }

        public bool ShouldShowMultiDrawTicketCostText()
        {
            // チケットがコストかつ引ける場合、チケットのコストを表示する
            return MultiDrawButtonViewModel.MultiDrawCostType == CostType.Item &&
                   MultiDrawButtonViewModel.IsEnoughMultiDrawCostItem &&
                   IsDrawableFlagByHasDrawLimitedCount;
        }

        public bool ShouldShowMultiGachaResources()
        {
            // 回数上限到達した場合、10連コスト表示を消す
            return IsDrawableFlagByHasDrawLimitedCount;
        }

        public bool ShouldShowMultiLimitedCountText()
        {
            // 利用可能回数表示
            return MultiDrawButtonViewModel.MultiDrawLimitCount != GachaDrawLimitCount.Unlimited;
        }

        public bool ShouldShowSingleDrawButtonCostArea()
        {
            // 単発ガシャボタンのコスト表示エリアを表示するか（ボタン自体が表示され、かつ回数上限未到達の場合）
            return SingleDrawButtonViewModel.IsDisplaySingleDrawButton && IsDrawableFlagByHasDrawLimitedCount;
        }

        public bool ShouldShowMultiDrawButtonCostArea()
        {
            // 10連ガシャボタンのコスト表示エリアを表示するか（ボタン自体が表示され、かつ回数上限未到達の場合）
            return MultiDrawButtonViewModel.IsDisplayMultiDrawButton && IsDrawableFlagByHasDrawLimitedCount;
        }

        public bool ShouldShowRatioButton()
        {
            return GachaType != GachaType.Medal &&
                   GachaType != GachaType.Tutorial;
        }

        public bool ShouldShowDetailButton()
        {
            return GachaContentDetailButtonFlag;
        }
    }
}
