using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.GachaList.Presentation.Views
{
    public class TutorialGachaButtonComponent : UIObject
    {
        [SerializeField] GameObject _buttonBackLighitEffect1;
        [SerializeField] GameObject _buttonFrontLighitEffect2;
        
        protected override void Awake()
        {
            base.Awake();
            SetButtonEffectActive(false);
        }

        public void SetButtonEffectActive(bool isActive)
        {
            _buttonBackLighitEffect1.SetActive(isActive);
            _buttonFrontLighitEffect2.SetActive(isActive);
        }
    }
}