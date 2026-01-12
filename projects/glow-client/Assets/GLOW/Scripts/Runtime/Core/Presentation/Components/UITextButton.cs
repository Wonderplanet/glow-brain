using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Core.Presentation.Components
{
    public class UITextButton : Button
    {
         public enum TitleColorTransition
        {
            UseTargetGraphicsColorTint,
            None,
            ColorTint,
        }

        public enum OptionalGraphicColorTransition
        {
            UseTargetGraphicsColorTint,
            None,
            ColorTint,
        }

        [SerializeField] UIText _titleText;
        [SerializeField] TitleColorTransition _titleColorTransition = TitleColorTransition.UseTargetGraphicsColorTint;
        [SerializeField] Color _titleDisableTintColor = new Color(0.784f, 0.784f, 0.784f, 0.5f);
        [SerializeField] OptionalGraphicColorTransition _optionalGraphicColorTransition = OptionalGraphicColorTransition.UseTargetGraphicsColorTint;
        [SerializeField] Color _optionalGraphicDisableTintColor = new Color(0.784f, 0.784f, 0.784f, 0.5f);

        protected override void Awake()
        {
            base.Awake();
            if (_titleText == null) _titleText = GetComponentInChildren<UIText>();
        }

        public UIText TitleText
        {
            get
            {
                if (_titleText == null)
                {
                    _titleText = GetComponentInChildren<UIText>();
                }
                return _titleText;
            }
        }

        public string Title
        {
            set
            {
                if (TitleText == null)
                {
                    Debug.LogWarning("UITitledButton : Title Text is none.");
                    return;
                }
                TitleText.SetText(value);
            }
        }

        protected override void DoStateTransition(SelectionState state, bool instant)
        {
            base.DoStateTransition(state, instant);

            switch (state)
            {
                case SelectionState.Normal:
                case SelectionState.Highlighted:
                case SelectionState.Pressed:
                case SelectionState.Selected:
                case SelectionState.Disabled:
                    TransiteTitleColor(state, instant);
                    TransiteOptionalGraphicsColorWithoutTitle(state, instant);
                    break;
            }
        }

        void TransiteTitleColor(SelectionState state, bool instant)
        {
            if (TitleText == null) return;

            switch (_titleColorTransition)
            {
                case TitleColorTransition.UseTargetGraphicsColorTint:
                    if (transition != Transition.ColorTint)
                    {
                        Debug.LogWarning("UITitltedButton : Button transition type is not color tint.");
                        return;
                    }
                    StartColorTween(TitleText, GetGraphicTintColor(state), instant);
                    break;
                case TitleColorTransition.ColorTint:
                    if (state == SelectionState.Disabled)
                    {
                        StartColorTween(TitleText, _titleDisableTintColor, instant);
                    }
                    else
                    {
                        Debug.LogWarning("UITitltedButton : TitleColorTransition.ColorTint only supports disabling");
                        StartColorTween(TitleText, GetGraphicTintColor(state), instant);
                    }
                    break;
                case TitleColorTransition.None: break;
            }
        }

        void StartColorTween(Graphic graphic, Color targetColor, bool instant)
        {
            if (graphic != null)
            {
                graphic.CrossFadeColor(targetColor, instant ? 0f : this.colors.fadeDuration, true, true);
            }
        }

        void StartColorTween(UIText uiText, Color targetColor, bool instant)
        {
            if (uiText != null)
            {
                uiText.CrossFadeColor(targetColor, instant ? 0f : this.colors.fadeDuration, true, true);
            }
        }

        void TransiteOptionalGraphicsColorWithoutTitle(SelectionState state, bool instant)
        {
            if (_optionalGraphicColorTransition == OptionalGraphicColorTransition.None) return;

            var graphics = GetComponentsInChildren<Graphic>(true);

            foreach (var g in graphics)
            {
                var tintColor = GetGraphicTintColorWithOptionalGraphicColorTransition(state);
                if (g == null) continue;
                if (g == targetGraphic) continue;
                if (TitleText.IsReferenceEqualsTextMeshProUGUI(g)) continue;
                StartColorTween(g, tintColor, instant);
            }
        }

        Color GetGraphicTintColorWithOptionalGraphicColorTransition(SelectionState state)
        {
            switch (_optionalGraphicColorTransition)
            {
                case OptionalGraphicColorTransition.UseTargetGraphicsColorTint:
                    if (transition != Transition.ColorTint)
                    {
                        Debug.LogWarning("UITitltedButton : Button transition type is not color tint.");
                        return GetGraphicTintColor(state);
                    }
                    return GetGraphicTintColor(state);//colors.disabledColor * colors.colorMultiplier;
                case OptionalGraphicColorTransition.ColorTint:
                    if (state == SelectionState.Disabled)
                    {
                        return _optionalGraphicDisableTintColor;
                    }
                    else
                    {
                        Debug.LogWarning("UITitltedButton : OptionalGraphicColorTransition.ColorTint only supports disabling");
                        return GetGraphicTintColor(state);
                    }
                default: return Color.white;
            }
        }

        Color GetGraphicTintColor(SelectionState state)
        {
            switch (state)
            {
                case SelectionState.Normal: return colors.normalColor * colors.colorMultiplier;
                case SelectionState.Highlighted: return colors.highlightedColor * colors.colorMultiplier;
                case SelectionState.Pressed: return colors.pressedColor * colors.colorMultiplier;
                case SelectionState.Selected: return colors.selectedColor * colors.colorMultiplier;
                case SelectionState.Disabled: return colors.disabledColor * colors.colorMultiplier;
                default: return Color.white;
            }
        }
    }
}
