using GLOW.Core.Domain.ValueObjects;
using UnityEngine;

namespace GLOW.Core.Presentation.Components
{
    public class IconGrade : UIObject
    {
        [SerializeField] UIImage[] _images;
        [SerializeField] Sprite _emptySprite;
        [SerializeField] Sprite[] _starSprites;

        public void SetGrade(int grade)
        {
            SetEmpty();

            // Grade0の場合は早期リターン
            if (grade == 0) return;

            for (int i = 0; i < _starSprites.Length; ++i)
            {
                var sprite = _starSprites[i];
                bool isFinish = false;
                for(int j = 0 ; j < _images.Length ; ++j)
                {
                    isFinish = grade <= (i * _images.Length + j + 1);
                    _images[j].Sprite = sprite;
                    if (isFinish) break;
                }

                if (isFinish) break;
            }
        }

        // TODO:原画効果対応での共通化の際に修正
        public void SetGrade(ArtworkGradeLevel grade)
        {
            SetEmpty();

            // Grade0の場合は早期リターン
            if (grade.IsEmpty()) return;

            for (int i = 0; i < _starSprites.Length; ++i)
            {
                var sprite = _starSprites[i];
                bool isFinish = false;
                for (int j = 0; j < _images.Length; ++j)
                {
                    isFinish = grade <= (i * _images.Length + j + 1);
                    _images[j].Sprite = sprite;
                    if (isFinish) break;
                }
                if (isFinish) break;
            }
        }

        void SetEmpty()
        {
            for (int i = 0; i < _images.Length; ++i)
            {
                _images[i].Sprite = _emptySprite;
            }
        }
    }
}
