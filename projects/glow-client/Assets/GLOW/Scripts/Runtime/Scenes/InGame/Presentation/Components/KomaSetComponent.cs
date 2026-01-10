using System.Collections.Generic;
using GLOW.Core.Modules.MultipleSwitchController;
using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Components
{
    public class KomaSetComponent : UIObject
    {
        [SerializeField] List<KomaComponent> _komaComponents;

        readonly MultipleSwitchController _fieldImageExpandingController = new ();

        public IReadOnlyList<KomaComponent> KomaComponents => _komaComponents;

        public Vector2 CenterPos => RectTransform.anchoredPosition - RectTransform.sizeDelta * 0.5f;

        protected override void Awake()
        {
            base.Awake();
            _fieldImageExpandingController.OnStateChanged = OnFieldImageExpandingStateChanged;
        }

        protected override void OnDestroy()
        {
            base.OnDestroy();
            _fieldImageExpandingController.Dispose();
        }

        public MultipleSwitchHandler ExpandFieldImage()
        {
            return _fieldImageExpandingController.TurnOn();
        }

        void OnFieldImageExpandingStateChanged(bool isOn)
        {
            foreach (var komaComponent in _komaComponents)
            {
                komaComponent.ExpandFieldImage(isOn);
            }
        }
    }
}
