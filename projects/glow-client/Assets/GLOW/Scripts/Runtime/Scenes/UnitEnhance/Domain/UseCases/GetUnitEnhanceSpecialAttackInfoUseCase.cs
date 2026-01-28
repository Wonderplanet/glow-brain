using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.UnitEnhance.Domain.Models;
using WonderPlanet.UnityStandard.Extension;
using Zenject;

namespace GLOW.Scenes.UnitEnhance.Domain.UseCases
{
    public class GetUnitEnhanceSpecialAttackInfoUseCase
    {
        [Inject] IGameRepository GameRepository { get; }

        public UnitEnhanceSpecialAttackInfoModel GetUnitEnhanceSpecialAttackInfoModel(UserDataId userUnitId)
        {
            var userUnit = GameRepository.GetGameFetchOther().UserUnitModels.Find(unit => unit.UsrUnitId == userUnitId);

            return new UnitEnhanceSpecialAttackInfoModel(userUnit.MstUnitId, userUnit.Grade, userUnit.Level);
        }
    }
}
