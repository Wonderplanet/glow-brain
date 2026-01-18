using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.OprData;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Scenes.GachaContent.Domain.Calculator;
using GLOW.Scenes.GachaContent.Domain.Model;
using GLOW.Scenes.GachaList.Domain.Evaluator;
using GLOW.Scenes.GachaList.Domain.Model;
using GLOW.Scenes.PassShop.Domain.Factory;
using WPFramework.Domain.Modules;
using Zenject;

namespace GLOW.Scenes.GachaContent.Domain.UseCases
{
    public class GachaListElementUseCaseModelFactory : IGachaListElementUseCaseModelFactory
    {
        [Inject] IOprGachaRepository OprGachaRepository { get; }
        [Inject] IOprGachaUpperRepository OprGachaUpperRepository { get; }
        [Inject] IOprGachaUseResourceRepository OprGachaUseResourceRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] ISystemInfoProvider SystemInfoProvider { get; }
        [Inject] IMstItemDataRepository MstItemDataRepository { get; }
        [Inject] IHeldAdSkipPassInfoModelFactory HeldAdSkipPassInfoModelFactory { get; }
        [Inject] IDailyResetTimeCalculator DailyResetTimeCalculator { get; }
        [Inject] IGachaEvaluator GachaEvaluator { get; }

        GachaListElementUseCaseModel IGachaListElementUseCaseModelFactory.Create(MasterDataId oprGachaId)
        {
            var oprGachaModel = OprGachaRepository.GetOprGachaModelFirstOrDefaultById(oprGachaId);

            return new GachaListElementUseCaseModel(
                CreateGachaFooterBannerUseCaseModel(oprGachaModel),
                CreateGachaContentAssetUseCaseModel(oprGachaModel),
                CreateGachaContentUseCaseModel(oprGachaModel));
        }

        GachaFooterBannerUseCaseModel CreateGachaFooterBannerUseCaseModel(OprGachaModel oprGachaModel)
        {
            // バナー
            var bannerAssetPath = GachaBannerAssetPath.FromAssetKey(oprGachaModel.GachaBannerAssetKey);
            return new GachaFooterBannerUseCaseModel(oprGachaModel.Id, bannerAssetPath);
        }

        GachaContentAssetUseCaseModel CreateGachaContentAssetUseCaseModel(OprGachaModel oprGachaModel)
        {
            // アセット
            var contentAssetPath = GachaContentAssetPath.FromAssetKey(oprGachaModel.GachaBannerAssetKey);
            return new GachaContentAssetUseCaseModel(contentAssetPath);
        }

        GachaContentUseCaseModel CreateGachaContentUseCaseModel(OprGachaModel gachaModel)
        {
            var now = TimeProvider.Now;
            var gameFetchModel = GameRepository.GetGameFetch();
            var gachaUseResourceModels = OprGachaUseResourceRepository.FindByGachaId(gachaModel.Id);
            var gameFetchOtherModel = GameRepository.GetGameFetchOther();
            var userGachaModel =
                gameFetchOtherModel.UserGachaModels.FirstOrDefault(model => model.OprGachaId == gachaModel.Id) ?? UserGachaModel.CreateById(gachaModel.Id);

            // 広告ガシャが引けるか
            var isAdGachaDrawable = GachaEvaluator.IsAdGachaDrawable(gachaModel, userGachaModel);
            var canAdGachaDraw = new AdGachaDrawableFlag(isAdGachaDrawable);

            // 04:00までの時間
            var remainingResetTime = DailyResetTimeCalculator.GetRemainingTimeToDailyReset();
            var adGachaResetRemainingTimeSpan = new AdGachaResetRemainingTimeSpan(remainingResetTime);
            var adGachaResetRemainingText = AdGachaResetRemainingText.GetAdGachaResetRemainingText(adGachaResetRemainingTimeSpan);

            // 広告ガシャの引ける回数
            var adGachaDrawableCount = GachaEvaluator.CalculateAdGachaDrawableCount(gachaModel, userGachaModel);

            // 回数制限到達時の判定
            var hasReachedDrawLimitedCount = GachaEvaluator.HasReachedDrawLimitedCount(gachaModel, userGachaModel);
            var isDrawableByDrawLimitedCount = new DrawableFlag(!hasReachedDrawLimitedCount);

            // 単発ガチャコスト
            var singleUseResourceModels = gachaUseResourceModels
                .Where(model => model.GachaDrawCount.Value == 1)
                .ToList();
            // 10蓮ガチャコスト
            var multiUseResourceModels = gachaUseResourceModels
                .Where(model => model.GachaDrawCount.Value > 1)
                .ToList();

            // 消費リソース取得 (消費リソースがない場合はプライオリティが低いものを返す)
            var singleUseResourceModel = GachaContentCalculator.GetHighestPriorityUseResourceModel(singleUseResourceModels, gameFetchModel, gameFetchOtherModel, SystemInfoProvider.GetApplicationSystemInfo().PlatformId);
            var multiUseResourceModel = GachaContentCalculator.GetHighestPriorityUseResourceModel(multiUseResourceModels, gameFetchModel, gameFetchOtherModel, SystemInfoProvider.GetApplicationSystemInfo().PlatformId);

            // 単発コスト設定がない
            var singleGachaDrawLimitedCount = GachaDrawLimitCount.Unlimited;
            var isDisplaySingleDrawButton = IsDisplayGachaDrawButton.True;
            if(singleUseResourceModel == null)
            {
                isDisplaySingleDrawButton = IsDisplayGachaDrawButton.False;
                singleUseResourceModel = OprGachaUseResourceModel.Empty;
            }
            else if(gachaModel.DailyPlayLimitCount.HasValue() || gachaModel.TotalPlayLimitCount.HasValue())
            {
                if (gachaModel.DailyPlayLimitCount.HasValue() && gachaModel.TotalPlayLimitCount.HasValue())
                {
                    var limitedCount = Math.Min(gachaModel.DailyPlayLimitCount.Value, gachaModel.TotalPlayLimitCount.Value);
                    singleGachaDrawLimitedCount = new GachaDrawLimitCount(limitedCount);
                }
                else if (gachaModel.DailyPlayLimitCount.HasValue())
                {
                    singleGachaDrawLimitedCount = gachaModel.DailyPlayLimitCount;
                }
                else if (gachaModel.TotalPlayLimitCount.HasValue())
                {
                    singleGachaDrawLimitedCount = gachaModel.TotalPlayLimitCount;
                }
            }

            var isEnoughSingleDrawItemCost = DrawableFlag.True;
            // 消費コストがチケットで単発のチケット不足時
            if (singleUseResourceModel.CostType == CostType.Item)
            {
                isEnoughSingleDrawItemCost = GachaEvaluator.IsGachaDrawable(
                    singleUseResourceModel,
                    gameFetchModel,
                    gameFetchOtherModel,
                    SystemInfoProvider.GetApplicationSystemInfo().PlatformId,
                    gachaModel,
                    userGachaModel
                );
            }

            // 10連コスト設定がない or 10連に引ける上限がある
            var multiGachaDrawLimitedCount = GachaDrawLimitCount.Unlimited;
            var isDisplayMultiDrawButton = IsDisplayGachaDrawButton.True;
            if(multiUseResourceModel == null)
            {
                isDisplayMultiDrawButton = IsDisplayGachaDrawButton.False;
                multiUseResourceModel = OprGachaUseResourceModel.Empty;
            }
            else if(gachaModel.DailyPlayLimitCount.HasValue() || gachaModel.TotalPlayLimitCount.HasValue())
            {
                if (gachaModel.DailyPlayLimitCount.HasValue() && gachaModel.TotalPlayLimitCount.HasValue())
                {
                    var limitedCount = Math.Min(gachaModel.DailyPlayLimitCount.Value, gachaModel.TotalPlayLimitCount.Value);
                    var drawableCount = limitedCount / multiUseResourceModel.GachaDrawCount.Value;
                    multiGachaDrawLimitedCount = new GachaDrawLimitCount(drawableCount);
                }
                else if (gachaModel.DailyPlayLimitCount.HasValue())
                {
                    var drawableCount = gachaModel.DailyPlayLimitCount.Value / multiUseResourceModel.GachaDrawCount.Value;
                    multiGachaDrawLimitedCount = new GachaDrawLimitCount(drawableCount);
                }
                else if (gachaModel.TotalPlayLimitCount.HasValue())
                {
                    var drawableCount = gachaModel.TotalPlayLimitCount.Value / multiUseResourceModel.GachaDrawCount.Value;
                    multiGachaDrawLimitedCount = new GachaDrawLimitCount(drawableCount);
                }
            }

            var isEnoughMultiDrawItemCost = DrawableFlag.True;
            // 消費コストがチケットで10連のチケット不足時
            if (multiUseResourceModel.CostType == CostType.Item)
            {
                isEnoughMultiDrawItemCost = GachaEvaluator.IsGachaDrawable(
                    multiUseResourceModel,
                    gameFetchModel,
                    gameFetchOtherModel,
                    SystemInfoProvider.GetApplicationSystemInfo().PlatformId,
                    gachaModel,
                    userGachaModel
                );
            }

            // 単発のコスト
            var singleDrawCostIconAssetPath = GachaContentCalculator.GetItemIconAssetPath(singleUseResourceModel.CostType, singleUseResourceModel.MstCostId, MstItemDataRepository);
            var singleDrawCostAmount = singleUseResourceModel.CostAmount;

            // 10連のコスト
            var multiDrawCostIconAssetPath = GachaContentCalculator.GetItemIconAssetPath(multiUseResourceModel.CostType, multiUseResourceModel.MstCostId, MstItemDataRepository);
            var multiDrawCostAmount = multiUseResourceModel.CostAmount;

            // 残り時間テキスト
            GachaRemainingTimeText gachaRemainingTimeText = GachaRemainingTimeText.CreateRemainingTimeText(gachaModel.EndAt, now, userGachaModel.GachaExpireAt);

            // 天井テキスト
            var userDrawCountThresholdModel = gameFetchOtherModel.UserGachaDrawCountThresholdModels.ToList();
            var gachaThresholdText = GachaContentCalculator.GetGachaContentThresholdText(gachaModel, OprGachaUpperRepository, userDrawCountThresholdModel, true);

            var heldAdSkipPassInfoModel = HeldAdSkipPassInfoModelFactory.CreateHeldAdSkipPassInfo();

            var gachaLogoAssetPath = GachaLogoAssetPath.FromAssetKey(gachaModel.GachaLogoAssetKey);

            var gachaContentDetailButtonFlag =
                gachaModel.AnnouncementId.IsEmpty() && gachaModel.GachaCautionId.IsEmpty()
                    ? GachaContentDetailButtonFlag.False
                    : GachaContentDetailButtonFlag.True;

            var pickupMstUnitIds = OprGachaRepository.GetOprGachaDisplayUnitI18nModelsById(gachaModel.Id)
                .Select(m => m.PickupMstUnitId)
                .ToList();

            return new GachaContentUseCaseModel(
                gachaModel.Id,
                isDrawableByDrawLimitedCount,
                gachaModel.GachaName,
                gachaModel.GachaType,
                gachaModel.EndAt,
                gachaContentDetailButtonFlag,
                pickupMstUnitIds,
                new IsDisplayGachaDrawButton(gachaModel.EnableAdPlay.Value),
                canAdGachaDraw,
                adGachaResetRemainingText,
                adGachaDrawableCount,
                gachaRemainingTimeText,
                gachaThresholdText,
                gachaModel.Description,
                singleUseResourceModel.CostType,
                singleGachaDrawLimitedCount,
                isDisplaySingleDrawButton,
                isEnoughSingleDrawItemCost,
                singleDrawCostIconAssetPath,
                singleDrawCostAmount,
                multiUseResourceModel.CostType,
                multiGachaDrawLimitedCount,
                isDisplayMultiDrawButton,
                isEnoughMultiDrawItemCost,
                multiDrawCostIconAssetPath,
                multiDrawCostAmount,
                heldAdSkipPassInfoModel,
                gachaModel.GachaFixedPrizeDescription,
                gachaModel.UnlockConditionType,
                gachaLogoAssetPath
            );
        }
    }
}
