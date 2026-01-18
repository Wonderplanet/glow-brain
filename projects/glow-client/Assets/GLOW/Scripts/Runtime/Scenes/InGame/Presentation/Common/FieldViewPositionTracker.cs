using Cysharp.Text;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Presentation.Field;
using UnityEngine;
using WPFramework.Modules.Log;

namespace GLOW.Scenes.InGame.Presentation.Common
{
    /// <summary>
    /// フィールド上のオブジェクト（キャラとか）の位置を追跡する
    /// </summary>
    public class FieldViewPositionTracker
    {
        readonly IFieldViewPositionTrackerTarget _target;
        readonly Transform _fieldViewTransform;
        readonly FieldViewConstructData _fieldViewConstructData;

        public FieldViewPositionTracker(
            IFieldViewPositionTrackerTarget target,
            Transform fieldViewTransform,
            FieldViewConstructData fieldViewConstructData)
        {
            _target = target;
            _fieldViewTransform = fieldViewTransform;
            _fieldViewConstructData = fieldViewConstructData;
        }

        public bool IsTargetDestroyed()
        {
            return _target.IsDestroyed() || _fieldViewTransform == null;
        }

        /// <summary>
        /// 追跡対象のコマUI上での位置を取得
        /// </summary>
        /// <returns></returns>
        public Vector2 GetUIPosition(KomaId komaId, RectTransform komaImageTransform, RectTransform uiTransform, Camera uiCamera)
        {
            if (_target.IsDestroyed()) return Vector2.zero;
            if (_fieldViewTransform == null) return Vector2.zero;

            if (!_fieldViewConstructData.KomaAreaDictionary.ContainsKey(komaId))
            {
                ApplicationLog.LogWarning(nameof(FieldViewPositionTracker), ZString.Format("{0} is not found in KomaAreaDictionary.", komaId));
                return Vector2.zero;
            }

            var targetViewPos =  _target.GetWorldPos();

            // 対象のフィールドView上の位置を求める
            var fieldViewPos = _fieldViewTransform.position;
            var pos = new Vector2(targetViewPos.x - fieldViewPos.x, targetViewPos.y - fieldViewPos.y);

            // コマサイズを1としたときの対象のコマ上の位置を求める
            var komaArea = _fieldViewConstructData.KomaAreaDictionary[komaId];
            var normalizedPosOnKoma = new Vector2(
                (pos.x - komaArea.xMin) / komaArea.width,
                (pos.y - komaArea.yMin) / komaArea.height);

            // コマImageのスクリーン座標を求める
            var komaImageWorldCorners = new Vector3[4];
            komaImageTransform.GetWorldCorners(komaImageWorldCorners);

            var komaImageScreenBottomLeft  = uiCamera.WorldToScreenPoint(komaImageWorldCorners[0]);
            var komaImageScreenTopRight = uiCamera.WorldToScreenPoint(komaImageWorldCorners[2]);

            // 対象のスクリーン座標を求める
            var targetScreenPos = new Vector2(
                komaImageScreenBottomLeft.x + normalizedPosOnKoma.x * (komaImageScreenTopRight.x - komaImageScreenBottomLeft.x),
                komaImageScreenBottomLeft.y + normalizedPosOnKoma.y * (komaImageScreenTopRight.y - komaImageScreenBottomLeft.y));

            // 指定UI上の対象の位置を求める
            RectTransformUtility.ScreenPointToLocalPointInRectangle(
                uiTransform,
                targetScreenPos,
                uiCamera,
                out Vector2 uiPos);

            return uiPos;
        }

        public KomaId GetKomaIdBelongTo()
        {
            var targetViewPos =  _target.GetWorldPos();

            // 対象のフィールドView上の位置を求める
            var fieldViewPos = _fieldViewTransform.position;
            var pos = new Vector2(targetViewPos.x - fieldViewPos.x, targetViewPos.y - fieldViewPos.y);

            foreach (var pair in _fieldViewConstructData.KomaAreaDictionary)
            {
                KomaId komaId = pair.Key;
                Rect komaArea = pair.Value;

                if (komaArea.Contains(pos))
                {
                    return komaId;
                }
            }

            return KomaId.Empty;
        }

        public Vector2 GetFieldUv()
        {
            if (_target.IsDestroyed()) return Vector2.zero;
            if (_fieldViewTransform == null) return Vector2.zero;

            var targetPos = _target.GetWorldPos();
            return FieldViewTextureUvCalculator.CalculateUv(_fieldViewTransform, _fieldViewConstructData, targetPos);
        }
    }
}
