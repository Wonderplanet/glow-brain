using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Battle;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;

namespace GLOW.Scenes.InGame.Domain.Models
{
    public interface IKomaEffectModel
    {
        KomaId KomaId { get; }
        KomaEffectType EffectType { get; }
        KomaEffectTargetSide TargetSide { get; }
        IReadOnlyList<CharacterColor> TargetColors { get; }
        IReadOnlyList<CharacterUnitRoleType> TargetRoles { get; }

        bool IsEffective();     // コマが持ってるコマ効果のIsEffectiveがfalseなら、そのコマは通常コマとして扱われる
        bool IsTarget(CharacterUnitModel characterUnitModel);
        IReadOnlyList<StateEffectType> GetStateEffectsThatBlockableThis();
        IReadOnlyList<StateEffectType> GetStateEffectsThatBoostThis();
        StateEffect GetStateEffect(BattleSide battleSide, IReadOnlyList<StateEffectParameter> boostParameters);
        bool ExistsStateEffect();
        bool CanSelectAsOutpostWeaponTarget();
        bool CanSelectAsSpecialUnitSummonTarget();
        bool IsAlwaysActive(); // true:コマに滞在中は毎F付与できるかどうかを確認するコマ効果 false:コマ突入時に1度だけ付与するコマ効果
        bool IsStateEffectVisible();

        /// <returns>(更新後のCharacterUnitModelのリスト, コマ効果をブロックしたキャラのリスト)</returns>
        (IReadOnlyList<CharacterUnitModel>, IReadOnlyList<FieldObjectId>) AffectCharacterUnits(
            IReadOnlyList<CharacterUnitModel> characterUnitModels,
            MstPageModel mstPageModel,
            ICoordinateConverter coordinateConverter,
            IStateEffectChecker stateEffectChecker);

        IKomaEffectModel GetUpdatedModel(KomaEffectUpdateContext context);
        IKomaEffectModel GetResetModel();
    }
}
