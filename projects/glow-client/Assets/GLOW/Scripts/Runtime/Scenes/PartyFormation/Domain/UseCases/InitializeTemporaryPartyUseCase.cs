using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.PartyFormation.Domain.Evaluator;
using GLOW.Scenes.PartyFormation.Domain.Models;
using Zenject;

namespace GLOW.Scenes.PartyFormation.Domain.UseCases
{
    public class InitializeTemporaryPartyUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IPartyCacheRepository PartyCacheRepository { get; }
        [Inject] IPreferenceRepository PreferenceRepository { get; }
        [Inject] IMstConfigRepository MstConfigRepository { get; }
        [Inject] IInGamePartySpecialRuleEvaluator InGamePartySpecialRuleEvaluator { get; }

        public PartyFormationInitializeModel InitializeTemporaryParty(MasterDataId specialRuleTargetMstId, InGameContentType specialRuleContentType)
        {
            var userPartyModels = GameRepository.GetGameFetchOther().UserPartyModels;
            var selectPartyNo = PreferenceRepository.SelectPartyNo;
            PartyCacheRepository.SetParties(userPartyModels, selectPartyNo);

            var currentParty = PartyCacheRepository.GetCurrentPartyModel();
            var partyNo = currentParty.PartyNo;
            var activePartySlot = PartyActiveCount.Max;
            var specialAssignLimit =
                new PartySpecialUnitAssignLimit(MstConfigRepository.GetConfig(MstConfigKey.PartySpecialUnitAssignLimit).Value.ToInt());

            return new PartyFormationInitializeModel(
                partyNo,
                currentParty.SlotCount,
                activePartySlot,
                specialAssignLimit,
                InGamePartySpecialRuleEvaluator.ExistsPartySpecialRule(specialRuleContentType, specialRuleTargetMstId)
            );
        }
    }
}
