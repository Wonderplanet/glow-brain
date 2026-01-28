using System.Collections.Generic;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.UnitEnhance.Domain.Models;

namespace GLOW.Scenes.UnitEnhance.Domain.ModelFactories
{
    public interface IUnitEnhanceAbilityModelListFactory
    {
        IReadOnlyList<UnitEnhanceAbilityModel> Create(MstCharacterModel mstCharacterModel, UnitRank rank);
    }
}
