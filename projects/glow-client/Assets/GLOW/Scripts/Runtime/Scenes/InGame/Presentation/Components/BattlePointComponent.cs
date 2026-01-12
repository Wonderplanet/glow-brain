using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Components
{
    public class BattlePointComponent : UIObject
    {
        [SerializeField] UIText _currentBattlePointText;
        [SerializeField] UIText _maxBattlePointText;

        public void SetBattlePoint(BattlePoint currentBp, BattlePoint maxBp)
        {
            _currentBattlePointText.SetText(currentBp.ToString());
            _maxBattlePointText.SetText(maxBp.ToString());
        }
    }
}
