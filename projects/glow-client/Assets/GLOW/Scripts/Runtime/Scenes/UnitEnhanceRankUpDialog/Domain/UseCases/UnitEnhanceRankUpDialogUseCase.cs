using System.Linq;
using GLOW.Core.Domain.Helper;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.UnitEnhance.Domain.ModelFactories;
using GLOW.Scenes.UnitEnhanceRankUpDialog.Domain.Models;
using WonderPlanet.UnityStandard.Extension;
using Zenject;

namespace GLOW.Scenes.UnitEnhanceRankUpDialog.Domain.UseCases
{
    public class UnitEnhanceRankUpDialogUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] IMstUnitRankUpRepository MstUnitRankUpRepository { get; }
        [Inject] IMstConfigRepository MstConfigRepository { get; }
        [Inject] IUnitStatusCalculateHelper UnitStatusCalculateHelper { get; }
        [Inject] IUnitEnhanceAbilityModelListFactory UnitEnhanceAbilityModelListFactory { get; }

        public UnitEnhanceRankUpDialogModel GetUnitEnhanceRankUpDialogModel(UserDataId userUnitId, UnitRank beforeRank, UnitRank afterRank)
        {
            var userUnit = GameRepository.GetGameFetchOther().UserUnitModels.Find(unit => unit.UsrUnitId == userUnitId);
            var mstUnit = MstCharacterDataRepository.GetCharacter(userUnit.MstUnitId);

            var before = MstUnitRankUpRepository.GetUnitRankUpList(mstUnit.UnitLabel)
                .Where(rank => beforeRank < rank.Rank)
                .OrderBy(rank => rank.Rank)
                .FirstOrDefault();

            var after = MstUnitRankUpRepository.GetUnitRankUpList(mstUnit.UnitLabel)
                .Where(rank => afterRank < rank.Rank)
                .OrderBy(rank => rank.Rank)
                .FirstOrDefault();

            var unitLevelCap = new UnitLevel(MstConfigRepository.GetConfig(MstConfigKey.UnitLevelCap).Value.ToInt());
            var beforeMaxLevel = unitLevelCap <= userUnit.Level || before == null ? unitLevelCap : before.RequireLevel;
            var afterMaxLevel = unitLevelCap <= userUnit.Level || after == null ? unitLevelCap : after.RequireLevel;

            var beforeStatus = UnitStatusCalculateHelper.Calculate(mstUnit, userUnit.Level, beforeRank, userUnit.Grade);
            var afterStatus = UnitStatusCalculateHelper.Calculate(mstUnit, userUnit.Level, afterRank, userUnit.Grade);

            var newlyAbilityModels = UnitEnhanceAbilityModelListFactory.Create(mstUnit, afterRank)
                .Where(abilityModel => afterRank == abilityModel.Ability.UnlockUnitRank)
                .ToList();

            return new UnitEnhanceRankUpDialogModel(
                mstUnit.AssetKey,
                mstUnit.RoleType,
                beforeMaxLevel,
                afterMaxLevel,
                beforeStatus.HP,
                afterStatus.HP,
                beforeStatus.AttackPower,
                afterStatus.AttackPower,
                newlyAbilityModels);
        }
    }
}
