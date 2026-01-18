using GLOW.Scenes.InGame.Presentation.Field;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Common
{
    public static class FieldViewTextureUvCalculator
    {
        public static Vector2 CalculateUv(FieldViewConstructData fieldViewConstructData, Vector2 posOnFieldView)
        {
            var fieldViewRect = fieldViewConstructData.FieldViewRect;

            return new Vector2(
                fieldViewRect.width != 0 ? (posOnFieldView.x - fieldViewRect.xMin) / fieldViewRect.width : 0,
                fieldViewRect.height != 0 ? (posOnFieldView.y - fieldViewRect.yMin) / fieldViewRect.height : 0);
        }

        public static Vector2 CalculateUv(Transform fieldViewTransform, FieldViewConstructData fieldViewConstructData, Vector2 worldPos)
        {
            var fieldViewPos = fieldViewTransform.position;
            var fieldViewRect = fieldViewConstructData.FieldViewRect;

            return new Vector2(
                fieldViewRect.width != 0 ? (worldPos.x - (fieldViewRect.xMin + fieldViewPos.x)) / fieldViewRect.width : 0,
                fieldViewRect.height != 0 ? (worldPos.y - (fieldViewRect.yMin + fieldViewPos.y)) / fieldViewRect.height : 0);
        }
    }
}
