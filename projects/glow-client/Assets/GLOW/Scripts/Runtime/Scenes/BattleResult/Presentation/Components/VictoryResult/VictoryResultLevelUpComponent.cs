using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.BattleResult.Presentation.Components
{
    public class VictoryResultLevelUpComponent : UIObject
    {
        [SerializeField] VictoryResultLevelUpFx _levelUpFx;

        // 今再生されているレベルアップ演出
        VictoryResultLevelUpFx _currentLevelUpFx;

        protected override void Awake()
        {
            base.Awake();
            _levelUpFx.Hidden = true;
            _currentLevelUpFx = null;
        }

        public void Play()
        {
            _currentLevelUpFx = Instantiate(_levelUpFx, transform);
            _currentLevelUpFx.Hidden = false;
        }

        public bool IsPlaying()
        {
            return _currentLevelUpFx != null;
        }
    }
}
