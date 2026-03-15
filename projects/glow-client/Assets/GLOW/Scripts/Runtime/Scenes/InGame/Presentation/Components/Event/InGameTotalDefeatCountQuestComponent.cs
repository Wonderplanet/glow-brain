using GLOW.Core.Presentation.Components;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Components
{
    public class InGameTotalDefeatCountQuestComponent : UIObject, IInGameDefeatEnemyCountDelegate
    {
        [SerializeField] InGameDefeatEnemyCountComponent _title;
        [SerializeField] InGameDefeatEnemyCountProgressComponent _progress;

        public void Initialize(DefeatEnemyCount endCondition)
        {
            _title.SetDefeatEnemyCount(endCondition);
            _progress.SetDefeatEnemyCountProgress(DefeatEnemyCount.Zero, endCondition);
        }

        void IInGameDefeatEnemyCountDelegate.UpdateDefeatEnemyCount(DefeatEnemyCount defeatedCount, DefeatEnemyCount endCondition)
        {
            _progress.SetDefeatEnemyCountProgress(defeatedCount, endCondition);
        }
    }
}
