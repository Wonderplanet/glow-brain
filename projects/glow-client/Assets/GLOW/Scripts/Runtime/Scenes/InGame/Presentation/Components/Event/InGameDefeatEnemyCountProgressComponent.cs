using GLOW.Core.Presentation.Components;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Components
{
    public class InGameDefeatEnemyCountProgressComponent : UIObject
    {
        [SerializeField] UIText _defeatEnemyCountText;

        public void SetDefeatEnemyCountProgress(DefeatEnemyCount defeatedEnemyCount, DefeatEnemyCount goalCount)
        {
            _defeatEnemyCountText.SetText("{0}/{1}", defeatedEnemyCount.Value, goalCount.Value);
        }
    }
}
