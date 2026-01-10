using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.Home.Presentation.Views
{
    public class HomeMainSpeedAttackRecord : UIObject
    {
        [SerializeField] UIText _speedAttackRecordText;
        [SerializeField] UIText _nextGoalTimeText;
        [SerializeField] GameObject _nextGoalTimeObject;
        [SerializeField] GameObject _speedAttackRecordObject;

        public void Setup(EventClearTimeMs clearTime, StageClearTime nextGoalTime)
        {
            if (nextGoalTime.IsEmpty())
            {
                _nextGoalTimeObject.SetActive(false);
                _speedAttackRecordObject.SetActive(true);
                _speedAttackRecordText.SetText("{0} <size=-6>秒</size>", clearTime.ToString());
            }
            else
            {
                _nextGoalTimeObject.SetActive(true);
                _speedAttackRecordObject.SetActive(false);
                _nextGoalTimeText.SetText("{0} <size=-6>秒</size>", nextGoalTime.ToString());
            }
        }
    }
}
