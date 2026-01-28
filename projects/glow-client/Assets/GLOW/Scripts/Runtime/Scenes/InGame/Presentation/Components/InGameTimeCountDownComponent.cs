using GLOW.Core.Presentation.Components;
using GLOW.Scenes.InGame.Presentation.ValueObjects;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Components
{
    public class InGameTimeCountDownComponent : UIObject
    {
        [SerializeField] GameObject _30s;
        [SerializeField] GameObject _20s;
        [SerializeField] GameObject _10s;
        [SerializeField] Animator _animator;

        static readonly int TriggerId = Animator.StringToHash("in");

        public void Play(TimeCountDown.EnumTimeCountDownType timeCountDownTypeType)
        {
            // 見せたいオブジェクト以外を非表示
            _30s.SetActive(timeCountDownTypeType==TimeCountDown.EnumTimeCountDownType.LeftTime30);
            _20s.SetActive(timeCountDownTypeType==TimeCountDown.EnumTimeCountDownType.LeftTime20);
            _10s.SetActive(timeCountDownTypeType==TimeCountDown.EnumTimeCountDownType.LeftTime10);
            // 再生
            _animator.SetTrigger(TriggerId);
        }
    }
}
