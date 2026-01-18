using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.BattleResult.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using WPFramework.Modules.Log;
using Zenject;

namespace GLOW.Scenes.BattleResult.Domain.Factory
{
    public class ResultScoreModelFactory : IResultScoreModelFactory
    {
        [Inject] IInGameScene InGameScene { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstQuestBonusUnitRepository MstQuestBonusUnitRepository { get; }
        [Inject] IPartyCacheRepository PartyCacheRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IOprCampaignRepository OprCampaignRepository { get; }

        public ResultScoreModel CreateResultScoreModel(
            GameFetchModel prevFetchModel,
            MstQuestModel mstQuest,
            IReadOnlyList<MasterDataId> oprCampaignIds)
        {
            var currentScore = InGameScene.ScoreModel.TotalScore;

            ResultScoreModel resultScoreModel;
            switch(mstQuest.QuestType)
            {
                case QuestType.Enhance:
                    var userStageEnhance = prevFetchModel.UserStageEnhanceModels.FirstOrDefault();
                    var currentHighScore = userStageEnhance != null
                        ? InGameScore.FromEnhanceQuestScore(userStageEnhance.MaxScore)
                        : InGameScore.Zero;
                    var isNewRecord = currentScore > currentHighScore;
                    var unitBonusPercentage = GetUnitBonusPercentage(mstQuest.Id);
                    var campaignBonusPercentage = GetCampaignBonusPercentage(oprCampaignIds);
                    var totalBonusPercentage = unitBonusPercentage + campaignBonusPercentage;

                    resultScoreModel = new ResultScoreModel(
                        currentScore,
                        isNewRecord ? currentScore : currentHighScore,
                        new NewRecordFlag(isNewRecord),
                        totalBonusPercentage);
                    break;
                default:
                    ApplicationLog.Log(
                        nameof(ResultScoreModelFactory),
                        $"スコア取得未対応のクエストタイプです:{mstQuest.QuestType}");
                    resultScoreModel = ResultScoreModel.Empty;
                    break;
            }

            return resultScoreModel;
        }

        /// <summary> 特定ユニットによるボーナス倍率を取得する </summary>
        EventBonusPercentage GetUnitBonusPercentage(MasterDataId mstQuestId)
        {
            var mstQuestBonusUnits = MstQuestBonusUnitRepository.GetQuestBonusUnits(mstQuestId)
                .Where(model => CalculateTimeCalculator.IsValidTime(
                    TimeProvider.Now,
                    model.StartAt,
                    model.EndAt))
                .ToList();

            var userUnits = GameRepository.GetGameFetchOther().UserUnitModels;
            var currentParty = PartyCacheRepository.GetCurrentPartyModel();

            return currentParty.GetUnitList()
                .Where(userUnitId => !userUnitId.IsEmpty())
                .Select(userUnitId =>
                    userUnits.FirstOrDefault(unit => unit.UsrUnitId == userUnitId, UserUnitModel.Empty))
                .Select(userUnit =>
                    mstQuestBonusUnits.FirstOrDefault(
                        mstBonus => mstBonus.MstUnitId == userUnit.MstUnitId,
                        MstQuestBonusUnitModel.Empty))
                .Aggregate(
                    EventBonusPercentage.Zero,
                    (n, next) => n + next.CoinBonusRate.ToEventBonusPercentage());
        }

        EventBonusPercentage GetCampaignBonusPercentage(IReadOnlyList<MasterDataId> oprCampaignIds)
        {
            if(oprCampaignIds.Count <= 0) return EventBonusPercentage.Zero;

            var campaignValue = OprCampaignRepository
                .GetOprCampaignModelByIds(oprCampaignIds)
                .Where(model => model.CampaignType == CampaignType.CoinDrop)
                .Select(model => model.EffectValue.Value - EventBonusPercentage.OneHundred.Value)
                .Sum();

            return new EventBonusPercentage(campaignValue);
        }
    }
}
