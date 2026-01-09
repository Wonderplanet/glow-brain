using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.PartyFormation.Domain.Evaluator;
using GLOW.Scenes.UnitList.Domain.Constants;
using GLOW.Scenes.UnitList.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.PartyFormation.Domain.UseCases
{
    public class SetupPartyFormationConditionalFilterUseCase
    {
        [Inject] IUnitSortFilterCacheRepository UnitSortFilterCacheRepository { get; }
        [Inject] IPartyCacheRepository PartyCacheRepository { get; }
        [Inject] IInGamePartySpecialRuleEvaluator InGamePartySpecialRuleEvaluator { get; }

        public UnitSortFilterCacheType Setup(MasterDataId specialRuleTargetMstId, InGameContentType specialRuleContentType)
        {
            var partyBonusUnits = PartyCacheRepository.GetBonusUnits();
            if (partyBonusUnits.IsEmpty())
            {
                var partyFormationModel = UnitSortFilterCacheRepository.GetModel(UnitSortFilterCacheType.PartyFormation);
                UnitSortFilterCacheRepository.UpdateBonusFilter(
                    UnitSortFilterCacheType.PartyFormation,
                    partyFormationModel.FilterBonusModel with
                    {
                        // イベントボーナスがない用の設定なのでFalseとする
                        EnableBonus = FilterBonusFlag.False,
                    });
                UnitSortFilterCacheRepository.UpdateFormationFilter(UnitSortFilterCacheType.PartyFormation,
                    partyFormationModel.FilterFormationModel with
                    {
                        EnableFormationFlag = new FilterFormationFlag(IsExistSpecialRule(specialRuleTargetMstId, specialRuleContentType)),
                    });

                return UnitSortFilterCacheType.PartyFormation;
            }

            var partyFormationWithEventBonusModel = UnitSortFilterCacheRepository.GetModel(UnitSortFilterCacheType.PartyFormationWithEventBonus);
            UnitSortFilterCacheRepository.UpdateBonusFilter(
                UnitSortFilterCacheType.PartyFormationWithEventBonus,
                partyFormationWithEventBonusModel.FilterBonusModel with
                {
                    EnableBonus = FilterBonusFlag.True,
                });
            UnitSortFilterCacheRepository.UpdateFormationFilter(UnitSortFilterCacheType.PartyFormationWithEventBonus,
                partyFormationWithEventBonusModel.FilterFormationModel with
                {
                    EnableFormationFlag = new FilterFormationFlag(IsExistSpecialRule(specialRuleTargetMstId, specialRuleContentType)),
                });

            return  UnitSortFilterCacheType.PartyFormationWithEventBonus;
        }

        bool IsExistSpecialRule(MasterDataId specialRuleTargetMstId, InGameContentType specialRuleContentType)
        {
            return InGamePartySpecialRuleEvaluator.ExistsPartySpecialRule(
                specialRuleContentType,
                specialRuleTargetMstId);
        }
    }
}
