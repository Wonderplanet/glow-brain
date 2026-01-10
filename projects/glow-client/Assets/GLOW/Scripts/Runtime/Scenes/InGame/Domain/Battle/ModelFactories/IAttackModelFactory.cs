using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models.AttackModel;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public interface IAttackModelFactory
    {
        (IAttackModel, IReadOnlyList<IStateEffectModel>) Create(
            FieldObjectId fieldObjectId,
            MasterDataId attackerCharacterId,
            StateEffectSourceId stateEffectSourceId,
            BattleSide battleSide,
            CharacterUnitRoleType roleType,
            CharacterColor color,
            OutpostCoordV2 pos,
            AttackPower baseAttackPower,
            HealPower healPower,
            CharacterColorAdvantageAttackBonus colorAdvantageAttackBonus,
            AttackBaseData attackBaseData,
            AttackElement attackElement,
            IReadOnlyList<IStateEffectModel> effects,
            MstPageModel mstPageModel,
            ICoordinateConverter coordinateConverter,
            IBuffStatePercentageConverter buffStatePercentageConverter);
    }
}
