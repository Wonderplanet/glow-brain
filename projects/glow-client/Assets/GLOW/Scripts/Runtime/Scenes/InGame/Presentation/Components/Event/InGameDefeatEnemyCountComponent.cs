using GLOW.Core.Presentation.Components;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Components
{
    public class InGameDefeatEnemyCountComponent : UIObject
    {
        [SerializeField] UIText _defeatEnemyCountText;

        public void SetDefeatEnemyCount(DefeatEnemyCount goalCount)
        {
            _defeatEnemyCountText.SetText("{0}", goalCount.Value);
        }
    }
}
