using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Calculator;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models.Campaign;
using GLOW.Core.Domain.Models.OprData;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using Zenject;
using GLOW.Core.Extensions;

namespace GLOW.Core.Domain.Factories
{
    public class CampaignModelFactory : ICampaignModelFactory
    {
        [Inject] IOprCampaignRepository OprCampaignRepository { get; }
        [Inject] ITimeProvider TimeProvider { get; }

        // targetId: MstQuestId or MstSeriesId
        // 現状SeriesIdは使用されないが、今後使用する予定あるかも

        public CampaignModel CreateCampaignModel(
            MasterDataId targetId,
            CampaignTargetType campaignTargetType,
            CampaignTargetIdType campaignTargetIdType,
            Difficulty difficulty,
            CampaignType campaignType)
        {
            var now = TimeProvider.Now;
            var oprCampaignModel = OprCampaignRepository.GetOprCampaignModelsByDataTime(now)
                .Where(m => CalculateTimeCalculator.IsValidTime(now, m.StartAt.Value, m.EndAt.Value))
                .Where(m => m.TargetId.IsEmpty() || m.TargetId == targetId)
                .Where(m => m.CampaignTargetType == campaignTargetType)
                .Where(m => m.CampaignTargetIdType == campaignTargetIdType)
                .Where(m => m.Difficulty == difficulty)
                .Where(m => m.CampaignType == campaignType)
                .FirstOrDefault(OprCampaignModel.Empty);

            if (oprCampaignModel.IsEmpty())
            {
                return CampaignModel.Empty;
            }

            // Title
            var title = GetCampaignTitle(oprCampaignModel.CampaignType);

            var remainingTimeSpan = CalculateTimeCalculator.GetRemainingTime(TimeProvider.Now, oprCampaignModel.EndAt.Value);
            return new CampaignModel(
                oprCampaignModel.CampaignType,
                title,
                oprCampaignModel.Description,
                remainingTimeSpan,
                oprCampaignModel.EffectValue,
                oprCampaignModel.CampaignTargetType);
        }

        public List<CampaignModel> CreateCampaignModels(
            MasterDataId targetId,
            CampaignTargetType campaignTargetType,
            CampaignTargetIdType campaignTargetIdType,
            Difficulty difficulty)
        {
            return OprCampaignRepository.GetOprCampaignModelsByDataTime(TimeProvider.Now)
                .Where(m => m.TargetId.IsEmpty() || m.TargetId == targetId)
                .Where(m => m.CampaignTargetType == campaignTargetType)
                .Where(m => m.CampaignTargetIdType == campaignTargetIdType)
                .Where(m => m.Difficulty == difficulty)
                .Select(opr => new CampaignModel(
                    opr.CampaignType,
                    GetCampaignTitle(opr.CampaignType),
                    opr.Description,
                    CalculateTimeCalculator.GetRemainingTime(TimeProvider.Now, opr.EndAt.Value),
                    opr.EffectValue,
                    opr.CampaignTargetType))
                .ToList();
        }

        public List<CampaignModel> CreateCampaignModels(CampaignTargetType campaignTargetType, CampaignTargetIdType campaignTargetIdType)
        {
            return OprCampaignRepository.GetOprCampaignModelsByDataTime(TimeProvider.Now)
                .Where(opr => opr.CampaignTargetType == campaignTargetType)
                .Where(opr => opr.CampaignTargetIdType == campaignTargetIdType)
                .Select(opr => new CampaignModel(
                    opr.CampaignType,
                    GetCampaignTitle(opr.CampaignType),
                    opr.Description,
                    CalculateTimeCalculator.GetRemainingTime(TimeProvider.Now, opr.EndAt.Value),
                    opr.EffectValue,
                    opr.CampaignTargetType))
                .ToList();
            
        }

        CampaignTitle GetCampaignTitle(CampaignType campaignType)
        {
            return campaignType switch
            {
                CampaignType.Stamina => new CampaignTitle("スタミナ"),
                CampaignType.Exp => new CampaignTitle("リーダーEXP"),
                CampaignType.ArtworkFragment => new CampaignTitle("原画のかけら"),
                CampaignType.ItemDrop => new CampaignTitle("アイテム"),
                CampaignType.CoinDrop => new CampaignTitle("コイン"),
                CampaignType.ChallengeCount => new CampaignTitle("挑戦回数"),
                _ => CampaignTitle.Empty
            };
        }
    }
}
