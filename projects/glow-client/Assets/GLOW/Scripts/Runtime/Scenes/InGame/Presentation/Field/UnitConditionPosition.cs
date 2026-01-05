using GLOW.Scenes.InGame.Presentation.Common;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Field
{
    public class UnitConditionPosition : IFieldViewPositionTrackerTarget
    {
        UnitImage _unitImage;
        
        public UnitConditionPosition(UnitImage unitImage)
        {
            _unitImage = unitImage;
        }
        
        Vector2 IFieldViewPositionTrackerTarget.GetWorldPos()
        {
            // この位置をベースにどのコマのいるかをIDで取得する処理にも使われている
            // 固定値を入れるとID取得ができずHPバーが生成されず、エラーになる
            var tagPosition = _unitImage.TagPosition.position;
            return new Vector2(tagPosition.x, tagPosition.y - 0.4f);
        }

        bool IFieldViewPositionTrackerTarget.IsDestroyed()
        {
            return _unitImage == null;
        }
    }
}