using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ModelFactories;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.OprData;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Modules.Tutorial.Domain.Definitions;
using GLOW.Scenes.GachaContent.Domain.Calculator;
using GLOW.Scenes.GachaList.Domain.Evaluator;
using GLOW.Scenes.GachaList.Domain.Model;
using GLOW.Scenes.PassShop.Domain.Factory;
using WPFramework.Domain.Modules;
using Zenject;

namespace GLOW.Scenes.GachaList.Domain.UseCases
{
    public class GachaListUseCase
    {
        [Inject] IOprGachaRepository OprGachaRepository { get; }
        [Inject] IOprGachaUpperRepository OprGachaUpperRepository { get; }
        [Inject] IOprGachaUseResourceRepository OprGachaUseResourceRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] ISystemInfoProvider SystemInfoProvider { get; }
        [Inject] IPlayerResourceModelFactory PlayerResourceModelFactory { get; }
        [Inject] IPreferenceRepository PreferenceRepository { get; }
        [Inject] IHeldAdSkipPassInfoModelFactory HeldAdSkipPassInfoModelFactory { get; }
        [Inject] IDailyResetTimeCalculator DailyResetTimeCalculator { get; }
        [Inject] IGachaEvaluator GachaEvaluator { get; }

        public GachaListUseCaseModel UpdateAndGetGachaListUseCaseModel()
        {
            // 端末に情報保存(副作用)
            PreferenceRepository.SetGachaListViewLastOpenedDateTimeOffset(TimeProvider.Now);

            var now = TimeProvider.Now;
            var gameFetchModel = GameRepository.GetGameFetch();
            var gameFetchOtherModel = GameRepository.GetGameFetchOther();
            var oprGachaModels = OprGachaRepository.GetOprGachaModelsByDataTime(now) ;
            // ガチャを優先度順にソート
            var sortedOprGachaModels = GachaEvaluator.SortOprGachaModelByPriority(oprGachaModels);

            // ガチャタイプごとにバナーモデル管理
            var festivalBannerModels = new List<FestivalGachaBannerModel>();
            var pickupBannerModels = new List<GachaBannerModel>();
            var freeBannerModels = new List<GachaBannerModel>();
            var ticketBannerModels = new List<GachaBannerModel>();
            var paidOnlyBannerModels = new List<GachaBannerModel>();
            var medalGachaModels = new List<MedalGachaModel>();
            var tutorialBannerModel = GachaBannerModel.Empty;
            PremiumGachaModel premiumGachaModel = PremiumGachaModel.Empty;

            foreach (var oprGachaModel in sortedOprGachaModels)
            {
                var userGachaModel = GameRepository.GetGameFetchOther().UserGachaModels
                    .FirstOrDefault(mode=> mode.OprGachaId == oprGachaModel.GachaId)
                                     ?? UserGachaModel.CreateById(oprGachaModel.GachaId);
                var gachaUseResourceModels = OprGachaUseResourceRepository.FindByGachaId(oprGachaModel.GachaId);

                // 表示条件を満たしていない場合は非表示にする
                if (!IsDisplayGachaBanner(
                        oprGachaModel,
                        gachaUseResourceModels,
                        userGachaModel,
                        gameFetchModel,
                        gameFetchOtherModel))
                {
                    continue;
                }

                // 無料・広告で引けるかのバッジ表示
                var isFreePlay = GachaEvaluator.IsFreePlay(oprGachaModel, userGachaModel);

                if (oprGachaModel.GachaType == GachaType.Normal)
                {
                    // ノーマルガシャは表示しない
                    continue;
                }

                if (oprGachaModel.GachaType == GachaType.Premium)
                {
                    premiumGachaModel = CreatePremiumGachaModel(
                        oprGachaModel,
                        userGachaModel,
                        gameFetchModel,
                        isFreePlay,
                        gachaUseResourceModels,
                        now);
                    continue;
                }

                if (oprGachaModel.GachaType == GachaType.Medal)
                {
                    var medalGachaModel = CreateMedalGachaModel(
                        oprGachaModel,
                        userGachaModel,
                        gameFetchModel,
                        gachaUseResourceModels,
                        now);
                    medalGachaModels.Add(medalGachaModel);
                }

                // 残り時間テキスト 開始からの時間制限がある場合を考慮する
                GachaRemainingTimeText gachaRemainingTimeText = GachaRemainingTimeText.CreateRemainingTimeText(
                    oprGachaModel.EndAt,
                    now,
                    userGachaModel.GachaExpireAt);

                var userDrawCountThresholdModels = gameFetchOtherModel.UserGachaDrawCountThresholdModels.ToList();
                var gachaThresholdText = GachaContentCalculator.GetGachaListThresholdText(
                    oprGachaModel,
                    OprGachaUpperRepository,
                    userDrawCountThresholdModels,
                    false);

                switch (oprGachaModel.GachaType)
                {
                    case GachaType.Festival:
                        var festivalGachaBannerModel = CreateFestivalGachaBannerModel(
                            oprGachaModel,
                            isFreePlay,
                            gachaRemainingTimeText,
                            gachaThresholdText);
                        festivalBannerModels.Add(festivalGachaBannerModel);
                        break;
                    case GachaType.Pickup:
                    case GachaType.Free:
                    case GachaType.Ticket:
                    case GachaType.PaidOnly:
                    case GachaType.Tutorial:
                        var gachaBannerModel = CreateGachaBannerModel(
                            oprGachaModel,
                            isFreePlay,
                            gachaRemainingTimeText,
                            gachaThresholdText);

                        switch (oprGachaModel.GachaType)
                        {
                            case GachaType.Pickup:
                                pickupBannerModels.Add(gachaBannerModel);
                                break;
                            case GachaType.Free:
                                freeBannerModels.Add(gachaBannerModel);
                                break;
                            case GachaType.Ticket:
                                ticketBannerModels.Add(gachaBannerModel);
                                break;
                            case GachaType.PaidOnly:
                                paidOnlyBannerModels.Add(gachaBannerModel);
                                break;
                            case GachaType.Tutorial:
                                // チュートリアル中のみ表示
                                if (gameFetchOtherModel.TutorialStatus == TutorialSequenceIdDefinitions.TutorialMainPart_start)
                                {
                                    tutorialBannerModel = gachaBannerModel;
                                }
                                break;
                        }
                        break;
                }
            }

            var heldAdSkipPassInfoModel = HeldAdSkipPassInfoModelFactory.CreateHeldAdSkipPassInfo();

            var useCaseModel = new GachaListUseCaseModel(
                festivalBannerModels,
                pickupBannerModels,
                freeBannerModels,
                ticketBannerModels,
                paidOnlyBannerModels,
                tutorialBannerModel,
                medalGachaModels,
                premiumGachaModel,
                heldAdSkipPassInfoModel
            );

            return useCaseModel;
        }

        bool HasResourceItem(
            OprGachaModel oprGachaModel,
            IReadOnlyList<OprGachaUseResourceModel> gachaUseResourceModels,
            UserGachaModel userGachaModel,
            GameFetchModel gameFetchModel,
            GameFetchOtherModel gameFetchOtherModel)
        {
            var platformId = SystemInfoProvider.GetApplicationSystemInfo().PlatformId;
            foreach (var useResourceModel in gachaUseResourceModels)
            {
                DrawableFlag drawableFlag = GachaEvaluator.IsGachaDrawable(
                    useResourceModel,
                    gameFetchModel,
                    gameFetchOtherModel,
                    platformId,
                    oprGachaModel,
                    userGachaModel
                );

                if (drawableFlag.Value)
                {
                    return true;
                }
            }
            return false;
        }

        PlayerResourceModel CreateCostPlayerResourceModel(OprGachaUseResourceModel model)
        {
            if (model == null)
            {
                return PlayerResourceModel.Empty;
            }

            switch (model.CostType)
            {
                case CostType.Item:
                    return PlayerResourceModelFactory.Create(
                        ResourceType.Item,
                        model.MstCostId,
                        PlayerResourceAmount.Empty
                    );

                case CostType.PaidDiamond:
                    return PlayerResourceModelFactory.Create(
                        ResourceType.PaidDiamond,
                        model.MstCostId,
                        PlayerResourceAmount.Empty
                    );

                case CostType.Diamond:
                    return PlayerResourceModelFactory.Create(
                        ResourceType.FreeDiamond,
                        model.MstCostId,
                        PlayerResourceAmount.Empty
                    );

                case CostType.Ad:
                case CostType.Free:
                default:
                    return PlayerResourceModel.Empty;
            }
        }

        PremiumGachaModel CreatePremiumGachaModel(
            OprGachaModel oprGachaModel,
            UserGachaModel userGachaModel,
            GameFetchModel gameFetchModel,
            bool isFreePlay,
            IReadOnlyList<OprGachaUseResourceModel> gachaUseResourceModels,
            DateTimeOffset now)
        {
            var gameFetchOtherModel = GameRepository.GetGameFetchOther();
            var platformId = SystemInfoProvider.GetApplicationSystemInfo().PlatformId;
            // 単発ガチャコストで降順ソート
            var singleUseResourceModels = gachaUseResourceModels
                .Where(model => model.GachaDrawCount?.Value == 1)
                .ToList();

            // 10蓮ガチャコストで降順ソート
            var multiUseResourceModels = gachaUseResourceModels
                .Where(model => model.GachaDrawCount?.Value > 1)
                .ToList();

            // 消費リソース取得 (消費リソースがない場合はプライオリティが低いものを返す)
            var singleUseResourceModel = GachaContentCalculator.GetHighestPriorityUseResourceModel(
                singleUseResourceModels,
                gameFetchModel,
                gameFetchOtherModel,
                platformId);

            var multiUseResourceModel = GachaContentCalculator.GetHighestPriorityUseResourceModel(
                multiUseResourceModels,
                gameFetchModel,
                gameFetchOtherModel,
                platformId);

            // 単発のコスト
            var singlePlayerResourceModel = CreateCostPlayerResourceModel(singleUseResourceModel);
            var singleDrawCostAmount = singleUseResourceModel.CostAmount;

            // 10連のコスト
            var multiPlayerResourceModel = CreateCostPlayerResourceModel(multiUseResourceModel);
            var multiDrawCostAmount = multiUseResourceModel.CostAmount;

            // 天井テキスト
            var userDrawCountThresholdModel = gameFetchOtherModel.UserGachaDrawCountThresholdModels
                .ToList();
            var gachaThresholdText =
                GachaContentCalculator.GetGachaListThresholdText(
                    oprGachaModel,
                    OprGachaUpperRepository,
                    userDrawCountThresholdModel, false);

            // 広告ガシャを引ける回数
            var adGachaDrawableCount = GachaEvaluator.CalculateAdGachaDrawableCount(oprGachaModel, userGachaModel);
            
            // リセットの04:00までの時間テキスト
            var timeSpan = DailyResetTimeCalculator.GetRemainingTimeToDailyReset();
            var adGachaResetRemainingTimeSpan = new AdGachaResetRemainingTimeSpan(timeSpan);
            var adResetRemainingText = AdGachaResetRemainingText.GetAdGachaResetRemainingText(adGachaResetRemainingTimeSpan);

            return new PremiumGachaModel(
                oprGachaModel.GachaId,
                GachaBannerAssetPath.FromAssetKey(oprGachaModel.GachaBannerAssetKey),
                oprGachaModel.Description,
                new NotificationBadge(isFreePlay),
                singleUseResourceModel.CostType,
                singlePlayerResourceModel,
                singleDrawCostAmount,
                multiPlayerResourceModel,
                multiDrawCostAmount,
                GachaEvaluator.IsAdGachaDrawable(oprGachaModel, userGachaModel),
                adResetRemainingText,
                adGachaDrawableCount,
                GachaRemainingTimeText.CreateRemainingTimeText(oprGachaModel.EndAt, now, userGachaModel.GachaExpireAt),
                gachaThresholdText,
                oprGachaModel.GachaFixedPrizeDescription
            );
        }

        MedalGachaModel CreateMedalGachaModel(
            OprGachaModel oprGachaModel,
            UserGachaModel userGachaModel,
            GameFetchModel gameFetchModel,
            IReadOnlyList<OprGachaUseResourceModel> gachaUseResourceModels,
            DateTimeOffset now)
        {
            var gameFetchOtherModel = GameRepository.GetGameFetchOther();
            var platformId = SystemInfoProvider.GetApplicationSystemInfo().PlatformId;
            // 単発ガチャコストで降順ソート
            var useResourceModels = gachaUseResourceModels
                .Where(model => model.GachaDrawCount.Value == 1)
                .ToList();
            OprGachaUseResourceModel useResourceModel =
                GachaContentCalculator.GetHighestPriorityUseResourceModel(
                    useResourceModels,
                    gameFetchModel,
                    gameFetchOtherModel,
                    platformId);
            var playerResourceModel = CreateCostPlayerResourceModel(useResourceModel);
            var drawCostAmount = useResourceModel.CostAmount;
            var userDrawCountThresholdModel = gameFetchOtherModel.UserGachaDrawCountThresholdModels
                .ToList();
            var gachaThresholdText = GachaContentCalculator.GetGachaListThresholdText(
                oprGachaModel,
                OprGachaUpperRepository,
                userDrawCountThresholdModel,
                false);
            var isDrawable = GachaEvaluator.IsGachaDrawable(
                useResourceModel,
                gameFetchModel,
                gameFetchOtherModel,
                platformId,
                oprGachaModel,
                userGachaModel
            );

            return new MedalGachaModel(
                oprGachaModel.GachaId,
                GachaBannerAssetPath.FromAssetKey(oprGachaModel.GachaBannerAssetKey),
                oprGachaModel.Description,
                playerResourceModel,
                drawCostAmount,
                isDrawable,
                GachaRemainingTimeText.CreateRemainingTimeText(oprGachaModel.EndAt, now, userGachaModel.GachaExpireAt),
                gachaThresholdText);
        }

        GachaBannerModel CreateGachaBannerModel(
            OprGachaModel oprGachaModel,
            bool isFreePlay,
            GachaRemainingTimeText remainingText,
            GachaThresholdText gachaThresholdText)
        {
            return new GachaBannerModel(
                oprGachaModel.GachaId,
                oprGachaModel.GachaType,
                GachaBannerAssetPath.FromAssetKey(oprGachaModel.GachaBannerAssetKey),
                new NotificationBadge(isFreePlay),
                remainingText,
                oprGachaModel.Description,
                oprGachaModel.GachaPriority,
                gachaThresholdText
            );
        }

        FestivalGachaBannerModel CreateFestivalGachaBannerModel(
            OprGachaModel oprGachaModel,
            bool isFreePlay,
            GachaRemainingTimeText remainingText,
            GachaThresholdText gachaThresholdText)
        {
            return new FestivalGachaBannerModel(
                oprGachaModel.GachaId,
                oprGachaModel.GachaType,
                FestivalGachaBannerAssetPath.FromAssetKey(oprGachaModel.GachaBannerAssetKey),
                new NotificationBadge(isFreePlay),
                remainingText,
                oprGachaModel.Description,
                gachaThresholdText
            );
        }

        bool IsDisplayGachaBanner(
            OprGachaModel oprGachaModel,
            IReadOnlyList<OprGachaUseResourceModel> gachaUseResourceModels,
            UserGachaModel userGachaModel,
            GameFetchModel gameFetchModel,
            GameFetchOtherModel gameFetchOtherModel)
        {
            // 開放条件を持ち、条件を満たしていない場合は表示しない
            if(!MeetsUnlockCondition(oprGachaModel, gameFetchOtherModel)) return false;

            // 開放期間をもち、開放期間外の場合は表示しない
            if (GachaEvaluator.IsExpiredUnlockDuration(
                    oprGachaModel.UnlockDurationHours,
                    userGachaModel.GachaExpireAt,
                    TimeProvider.Now))
            {
                return false;
            }

            // チケット/メダルガシャで未所持時非表示設定の場合表示しない
            if ((oprGachaModel.GachaType == GachaType.Ticket || oprGachaModel.GachaType == GachaType.Medal) &&
                oprGachaModel.AppearanceCondition == AppearanceCondition.HasTicket &&
                !HasResourceItem(
                    oprGachaModel,
                    gachaUseResourceModels,
                    userGachaModel,
                    gameFetchModel,
                    gameFetchOtherModel))
            {
                return false;
            }

            // 有償限定ガチャかつ上限まで引いている場合は表示しない
            if (oprGachaModel.GachaType == GachaType.PaidOnly &&
                GachaEvaluator.HasReachedDrawLimitedCount(oprGachaModel,userGachaModel))
            {
                return false;
            }

            return true;
        }

        bool MeetsUnlockCondition(OprGachaModel oprGachaModel, GameFetchOtherModel gameFetchOtherModel)
        {
            // 条件がない場合は表示する
            if(oprGachaModel.UnlockConditionType == GachaUnlockConditionType.None) return true;

            // チュートリアルメインパート完了済みの場合は表示する
            if (oprGachaModel.UnlockConditionType == GachaUnlockConditionType.MainPartTutorialComplete &&
                gameFetchOtherModel.TutorialStatus.IsCompleted())
            {
                return true;
            }

            return false;
        }


    }
}
