using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.HomePartyFormation.Domain.Evaluators;
using Zenject;
namespace GLOW.Scenes.HomePartyFormation.Domain.UseCases
{
    public class RecommendPartyFormationUseCase
    {
        [Inject] IPartyCacheRepository PartyCacheRepository { get; }
        [Inject] IRecommendPartyFormationEvaluator RecommendPartyFormationEvaluator { get; }

        public RecommendPartyFormationCompleteFlag FormRecommendParty(
            PartyNo partyNo,
            EventBonusGroupId eventBonusGroupId,
            MasterDataId mstSpecialRuleTargetId,
            InGameContentType contentType,
            MasterDataId enhanceQuestId)
        {
            // パーティのスロット数を超える場合は、スロット数に合わせる
            var party = PartyCacheRepository.GetCacheParty(partyNo);
            var resultUnits = RecommendPartyFormationEvaluator.GetRecommendPartyFormationUnits(
                eventBonusGroupId,
                mstSpecialRuleTargetId,
                contentType,
                enhanceQuestId,
                party.SlotCount);

            // 編成
            if (resultUnits.Count == 0) return RecommendPartyFormationCompleteFlag.False;

            // アサイン
            var userUnitIds = resultUnits.Select(unit => unit.UsrUnitId).ToList();
            PartyCacheRepository.UpdateParty(partyNo, party.PartyName, userUnitIds);

            return RecommendPartyFormationCompleteFlag.True;
        }
    }
}
