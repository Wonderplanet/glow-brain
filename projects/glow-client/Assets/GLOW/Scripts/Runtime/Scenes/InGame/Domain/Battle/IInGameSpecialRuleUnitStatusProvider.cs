using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.InGame.Domain.Models;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public interface IInGameSpecialRuleUnitStatusProvider
    {
        SpecialRuleUnitStatusParameterModel
            GetSpecialRuleUnitStatus(
                MasterDataId mstUnitId,
                IReadOnlyList<MstInGameSpecialRuleUnitStatusModel> specialRuleUnitStatusModels);

        SpecialRuleUnitStatusParameterModel
            GetSpecialRuleUnitStatus(
                MstCharacterModel mstCharacter,
                IReadOnlyList<MstInGameSpecialRuleUnitStatusModel> specialRuleUnitStatusModels);
    }
}
