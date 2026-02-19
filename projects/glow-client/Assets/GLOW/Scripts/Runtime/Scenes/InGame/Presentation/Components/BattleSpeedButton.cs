using GLOW.Core.Presentation.Components;
using GLOW.Scenes.InGame.Domain.Constants;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Components
{
    public class BattleSpeedButton : UIObject
    {
        [SerializeField] GameObject[] _speedGameObjects;   // 各倍速用

        public void SetBattleSpeed(BattleSpeed battleSpeed)
        {
            var index = battleSpeed switch {
                BattleSpeed.x1 => 0,
                BattleSpeed.x1_5 => 1,
                BattleSpeed.x2 => 2,
                BattleSpeed.x3 => 3,
                _ => 0
            };

            for (var i = 0; i < _speedGameObjects.Length; i++)
            {
                _speedGameObjects[i].SetActive(i == index);
            }
        }
    }
}
