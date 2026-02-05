using GLOW.Modules.Spine.Presentation;
using GLOW.Scenes.InGame.Presentation.Constants;
using GLOW.Scenes.InGame.Presentation.ValueObjects;
using UnityEngine.Playables;

namespace GLOW.Scenes.IdleIncentiveTop.Presentation.TimeLineTracks
{
    public class IdleIncentiveUnitAnimationTrackBehaviour : PlayableBehaviour
    {
        public UnitAnimationType UnitAnimationType { get; set; }

        public override void ProcessFrame(Playable playable, FrameData info, object playerData)
        {
            var avatar = playerData as BattleEffectUsableUISpineWithOutlineAvatar;
            if (avatar == null)
            {
                return;
            }

            switch (UnitAnimationType)
            {
                case UnitAnimationType.Wait:
                    if (avatar.GetCurrentAnimationStateName() == CharacterUnitAnimation.Wait.Name) return;
                    avatar.StartAnimation(CharacterUnitAnimation.Wait, CharacterUnitAnimation.Empty);
                    break;
                case UnitAnimationType.Move:
                    if (avatar.GetCurrentAnimationStateName() == CharacterUnitAnimation.Move.Name) return;
                    avatar.StartAnimation(CharacterUnitAnimation.Move, CharacterUnitAnimation.Empty);
                    break;
                case UnitAnimationType.Attack:
                    if (avatar.GetCurrentAnimationStateName() == CharacterUnitAnimation.Attack.Name) return;
                    avatar.StartAnimation(CharacterUnitAnimation.Attack, CharacterUnitAnimation.Empty);
                    break;
                case UnitAnimationType.SpecialAttackCutIn:
                    if (avatar.GetCurrentAnimationStateName() == CharacterUnitAnimation.SpecialAttackCutIn.Name) return;
                    avatar.StartAnimation(CharacterUnitAnimation.SpecialAttackCutIn, CharacterUnitAnimation.Empty);
                    break;
                case UnitAnimationType.Death:
                    if (avatar.GetCurrentAnimationStateName() == CharacterUnitAnimation.Death.Name) return;
                    avatar.StartAnimation(CharacterUnitAnimation.Death, CharacterUnitAnimation.Empty);
                    break;
                case UnitAnimationType.Appearing:
                    if (avatar.GetCurrentAnimationStateName() == CharacterUnitAnimation.Appearing.Name) return;
                    avatar.StartAnimation(CharacterUnitAnimation.Appearing, CharacterUnitAnimation.Empty);
                    break;
            }
        }
    }
}
