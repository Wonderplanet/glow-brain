using System;
using System.Diagnostics.CodeAnalysis;
using UnityEngine;

namespace GLOW.Core.Presentation.Components
{
    public class UIToggleableComponentGroup : UIObject
    {
        [Serializable]
        [SuppressMessage("ReSharper", "InconsistentNaming")]
        public struct ToggleableComponentInfo
        {
            public string Key;
            public UIToggleableComponent ToggleableComponent;
        }

        [SerializeField] string _defaultToggleOnKey;
        [SerializeField] ToggleableComponentInfo[] _toggleableComponentInfos;

        protected override void Awake()
        {
            base.Awake();
            SetToggleOn(_defaultToggleOnKey);
        }

        public void SetToggleOn(string key)
        {
            foreach (var toggleableComponentInfo in _toggleableComponentInfos)
            {
                toggleableComponentInfo.ToggleableComponent.IsToggleOn = toggleableComponentInfo.Key == key;
            }
        }
    }
}
