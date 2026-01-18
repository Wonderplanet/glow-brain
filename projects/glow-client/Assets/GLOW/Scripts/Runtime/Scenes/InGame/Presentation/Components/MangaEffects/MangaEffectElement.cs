using GLOW.Core.Presentation.Components;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Components
{
    public class MangaEffectElement : UIObject
    {
        [SerializeField] Vector2 _randomPositionRange;

        protected override void Awake()
        {
            base.Awake();
            SetRandomPosition();
        }

        public void Flip()
        {
            var myTransform = transform;

            var pos = myTransform.localPosition;
            pos.x *= -1;

            myTransform.localPosition = pos;
        }

        void SetRandomPosition()
        {
            var position = transform.localPosition;

            position.x += Random.Range(-_randomPositionRange.x, _randomPositionRange.x);
            position.y += Random.Range(-_randomPositionRange.y, _randomPositionRange.y);

            transform.localPosition = position;
        }
    }
}
