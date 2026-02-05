using System.Collections.Generic;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Campaign;
using GLOW.Core.Domain.Models.Pvp;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.PvpTop.Domain.Resolver;
using GLOW.Scenes.PvpTop.Domain.ValueObject;
using GLOW.Scenes.QuestContentTop.Domain.enums;
using GLOW.Scenes.QuestContentTop.Domain.UseCaseModel;
using GLOW.Scenes.QuestContentTop.Domain.ValueObject;
using Zenject;

namespace GLOW.Scenes.QuestContentTop.Domain.Factory
{
    public class QuestContentTopPvpModelFactory : IQuestContentTopPvpModelFactory
    {
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IDailyResetTimeCalculator DailyResetTimeCalculator { get; }
        [Inject] IPvpChallengeStatusFactory PvpChallengeStatusFactory { get; }
        [Inject] IMstCurrentPvpModelResolver MstCurrentPvpModelResolver { get; }
        [Inject] IPvpQuestContentOpeningStatusModelFactory PvpQuestContentOpeningStatusModelFactory { get; }

        IReadOnlyList<QuestContentTopElementUseCaseModel>
            IQuestContentTopPvpModelFactory.CreatePvpQuestContentTopElementUseCaseModels()
        {
            var seasonModel = GameRepository.GetGameFetchOther().SysPvpSeasonModel;

            var userPvpStatusModel = GameRepository.GetGameFetchOther().UserPvpStatusModel;
            var mstPvpModel = MstCurrentPvpModelResolver.CreateMstPvpModel(seasonModel.Id);
            return new List<QuestContentTopElementUseCaseModel>
            {
                CreateQuestContentTopElementUseCaseModel(
                    userPvpStatusModel,
                    mstPvpModel,
                    seasonModel
                )
            };
        }

        QuestContentTopElementUseCaseModel CreateQuestContentTopElementUseCaseModel(
            UserPvpStatusModel userPvpStatusModel,
            MstPvpModel mstPvpModel,
            SysPvpSeasonModel seasonModel
            )
        {
            var questContentOpeningStatusModel =
                PvpQuestContentOpeningStatusModelFactory.Create();

            var pvpChallengeStatus =
                PvpChallengeStatusFactory.Create(mstPvpModel.ItemChallengeCost, userPvpStatusModel);

            // 開催・開放されているか
            var isOpening = questContentOpeningStatusModel.IsOpening();
            var contentBadge = pvpChallengeStatus.IsChallengeable() && isOpening
                ? NotificationBadge.True
                : NotificationBadge.False;

            return new QuestContentTopElementUseCaseModel(
                QuestContentTopElementType.Pvp,
                questContentOpeningStatusModel,
                pvpChallengeStatus.RemainingChallengeCount,
                pvpChallengeStatus.ToQuestContentTopChallengeType(),
                CreateQuestChallengeResetTime(),
                CreateRemainingTimeSpan(seasonModel),
                HasRankingFlag.False, //ContentTop上でランキング出すときは計算が必要かも。今はfalse
                NotificationBadge.False, //ContentTop上でランキング出すときは計算が必要かも。今はfalse
                contentBadge,
                MasterDataId.Empty, //MstEventIdと関わりは無いので、Empty
                mstPvpModel.Name.ToEventName(),
                EventContentBannerAssetPath.Empty, //特別シーズンごとに画像切り替えは無いのでEmpty
                new List<CampaignModel>() //キャンペーン無いものとして空を入れている
            );
        }


        QuestChallengeResetTime CreateQuestChallengeResetTime()
        {
            return new QuestChallengeResetTime(DailyResetTimeCalculator.GetRemainingTimeToDailyReset());
        }

        RemainingTimeSpan CreateRemainingTimeSpan(SysPvpSeasonModel sysPvpSeasonModel)
        {
            return new RemainingTimeSpan(sysPvpSeasonModel.EndAt - TimeProvider.Now);
        }
    }
}
