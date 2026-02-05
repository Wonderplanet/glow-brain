using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.BattleResult.Presentation.Components
{
    public class VictoryResultUserLevelComponent : UIObject
    {
        [SerializeField] UIText _userLevelText;
        [SerializeField] Animator _animator;
        [SerializeField] string _levelUpAnimationStateName;

        public void SetLevel(UserLevel level, bool isLevelUp)
        {
            _userLevelText.SetText(level.ToStringAmount());

            if (isLevelUp)
            {
                _animator.Play(_levelUpAnimationStateName);
            }
        }
    }
}
