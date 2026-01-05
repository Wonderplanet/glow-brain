using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Components
{
    public class InGameDefeatTargetEnemyQuestComponent : UIObject, IInGameDefeatTargetEnemyDelegate
    {
        [SerializeField] UIText _title;
        [SerializeField] UIText _remaining;

        public void Initialize(CharacterName characterName, DefeatEnemyCount endCondition)
        {
            var format = "{0}を{1}体撃破しよう！";
            _title.SetText(format, characterName.Value, endCondition.Value);
            SetRemainingCount(DefeatEnemyCount.Zero, endCondition);
        }

        void IInGameDefeatTargetEnemyDelegate.UpdateRemainingEnemyCount(DefeatEnemyCount defeatCount, DefeatEnemyCount endCondition)
        {
            SetRemainingCount(defeatCount, endCondition);
        }

        void SetRemainingCount(DefeatEnemyCount defeatCount, DefeatEnemyCount endCondition)
        {
            var format = "{0}/{1}";
            _remaining.SetText(format, defeatCount.Value, endCondition.Value);
        }
    }
}
