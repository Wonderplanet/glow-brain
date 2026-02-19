using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Battle;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public record BaseKomaEffectModel(
        KomaId KomaId,
        KomaEffectType EffectType,
        KomaEffectTargetSide TargetSide,
        IReadOnlyList<CharacterColor> TargetColors,
        IReadOnlyList<CharacterUnitRoleType> TargetRoles) : IKomaEffectModel
    {
        protected static readonly IReadOnlyList<FieldObjectId> EmptyFieldObjectIdList = Array.Empty<FieldObjectId>();

        public virtual bool IsEffective()
        {
            return true;
        }

        public virtual bool IsTarget(CharacterUnitModel characterUnitModel)
        {
            return IsTargetBattleSide(characterUnitModel.BattleSide)
                   && TargetColors.Contains(characterUnitModel.Color)
                   && TargetRoles.Contains(characterUnitModel.RoleType);
        }

        public virtual IReadOnlyList<StateEffectType> GetStateEffectsThatBlockableThis()
        {
            return Array.Empty<StateEffectType>();
        }

        public virtual IReadOnlyList<StateEffectType> GetStateEffectsThatBoostThis()
        {
            return Array.Empty<StateEffectType>();
        }

        public virtual StateEffect GetStateEffect(BattleSide battleSide, IReadOnlyList<StateEffectParameter> boostParameters)
        {
            return StateEffect.Empty;
        }

        public virtual bool ExistsStateEffect()
        {
            return false;
        }

        public virtual bool CanSelectAsOutpostWeaponTarget()
        {
            return true;
        }

        public virtual bool CanSelectAsSpecialUnitSummonTarget()
        {
            return true;
        }

        public virtual bool IsAlwaysActive()
        {
            return false;
        }

        public virtual bool IsStateEffectVisible()
        {
            return false;
        }

        public virtual (IReadOnlyList<CharacterUnitModel>, IReadOnlyList<FieldObjectId>) AffectCharacterUnits(
            IReadOnlyList<CharacterUnitModel> characterUnitModels,
            MstPageModel mstPageModel,
            ICoordinateConverter coordinateConverter,
            IStateEffectChecker stateEffectChecker)
        {
            return (characterUnitModels, EmptyFieldObjectIdList);
        }

        public virtual IKomaEffectModel GetUpdatedModel(KomaEffectUpdateContext context)
        {
            return this;
        }

        public virtual IKomaEffectModel GetResetModel()
        {
            return this;
        }

        protected virtual bool IsTargetBattleSide(BattleSide battleSide)
        {
            return TargetSide == KomaEffectTargetSide.All
                   || TargetSide == KomaEffectTargetSide.Player && battleSide == BattleSide.Player
                   || TargetSide == KomaEffectTargetSide.Enemy && battleSide == BattleSide.Enemy;
        }
    }
}
