using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.IdleIncentiveTop.Domain.Models;

namespace GLOW.Scenes.IdleIncentiveTop.Domain.ModelFactories
{
    public class IdleIncentiveTopPlayerUnitModelFactory : IIdleIncentiveTopPlayerUnitModelFactory
    {
        public IdleIncentiveTopPlayerUnitModel Create(MstCharacterModel mstCharacterModel)
        {
            var mainAttackElement = mstCharacterModel.NormalMstAttackModel.AttackData.MainAttackElement;
            var attackRange = mainAttackElement.AttackRange;
            var attackDelay = mstCharacterModel.NormalMstAttackModel.AttackData.AttackDelay;
            var unitImageAssetPath = UnitImageAssetPath.FromAssetKey(mstCharacterModel.AssetKey);

            return new IdleIncentiveTopPlayerUnitModel(
                mstCharacterModel.AssetKey,
                unitImageAssetPath,
                mstCharacterModel.RoleType,
                attackDelay,
                attackRange);
        }
    }
}