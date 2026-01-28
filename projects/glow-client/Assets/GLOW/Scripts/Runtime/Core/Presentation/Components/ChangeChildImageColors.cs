using UnityEngine;
using UnityEngine.UI;

namespace GLOW.Core.Presentation.Components
{
    [ExecuteInEditMode]
    public class ChangeChildImageColors : MonoBehaviour
    {
        public Color targetColor;

        void Start()
        {
            if (Application.isPlaying)
            {
                ChangeColors(transform, targetColor);
            }
        }

        void OnValidate()
        {
            if (this.enabled)
            {
                ChangeColors(transform, targetColor);
            }
            else
            {
                ResetColors(transform);
            }
        }

        void OnEnable()
        {
            ChangeColors(transform, targetColor);
        }

        void OnDisable()
        {
            ResetColors(transform);
        }

        void ChangeColors(Transform parent, Color color)
        {
            foreach (Transform child in parent)
            {
                Image image = child.GetComponent<Image>();
                if (image != null)
                {
                    image.color = color;
                }

                if (child.childCount > 0)
                {
                    ChangeColors(child, color);
                }
            }
        }

        void ResetColors(Transform parent)
        {
            foreach (Transform child in parent)
            {
                Image image = child.GetComponent<Image>();
                if (image != null)
                {
                    image.color = Color.white;
                }

                if (child.childCount > 0)
                {
                    ResetColors(child);
                }
            }
        }
    }
}
