using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.UnitEnhance;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.UnitEnhance.Domain.Models;
using WonderPlanet.UnityStandard.Extension;
using Zenject;

namespace GLOW.Scenes.UnitEnhance.Domain.ModelFactories
{
    public class UnitEnhanceAbilityModelListFactory : IUnitEnhanceAbilityModelListFactory
    {
        [Inject] IMstUnitRankUpRepository MstUnitRankUpRepository { get; }
        [Inject] IMstUnitSpecificRankUpRepository MstUnitSpecificRankUpRepository { get; }
        [Inject] IMstConfigRepository MstConfigRepository { get; }

        public IReadOnlyList<UnitEnhanceAbilityModel> Create(MstCharacterModel mstCharacterModel, UnitRank rank)
        {
            return mstCharacterModel.MstUnitAbilityModels
                .Select(ability =>
                {
                    if (ability.IsEmpty || ability.UnitAbility.IsEmpty())
                    {
                        return UnitEnhanceAbilityModel.Empty;
                    }

                    var unlockLevel = GetUnlockNextLevelCap(mstCharacterModel, ability.UnitAbility.UnlockUnitRank);
                    var isLock = rank < ability.UnitAbility.UnlockUnitRank;
                    return new UnitEnhanceAbilityModel(
                        ability.UnitAbility,
                        unlockLevel,
                        isLock);
                })
                .ToList();
        }

        UnitLevel GetUnlockNextLevelCap(MstCharacterModel mstCharacterModel, UnitRank rank)
        {
            var unitLevelCap = MstConfigRepository.GetConfig(MstConfigKey.UnitLevelCap).Value.ToUnitLevel();
            if (mstCharacterModel.HasSpecificRankUp)
            {
                var unitSpecificRankUp = MstUnitSpecificRankUpRepository
                    .GetUnitSpecificRankUpList(mstCharacterModel.Id)
                    .MinByAboveLowerLimit(x => x.Rank, rank) ?? MstUnitSpecificRankUpModel.Empty;

                if (unitSpecificRankUp.IsEmpty())
                {
                    return unitLevelCap;
                }

                return unitSpecificRankUp.RequireLevel;
            }
            else
            {
                var unitRankUp = MstUnitRankUpRepository
                    .GetUnitRankUpList(mstCharacterModel.UnitLabel)
                    .MinByAboveLowerLimit(x => x.Rank, rank) ?? MstUnitRankUpModel.Empty;

                if (unitRankUp.IsEmpty())
                {
                    return unitLevelCap;
                }

                return unitRankUp.RequireLevel;
            }
        }
    }
}
