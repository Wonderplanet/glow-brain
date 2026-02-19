using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects.InGame;
using UnityEngine;

namespace GLOW.Scenes.InGame.Presentation.Field
{
    /// <summary>
    /// バトルフィールドViewを構成するためのデータ
    ///
    /// バトルフィールドは味方拠点から敵拠点まで一直線のひと続きのフィールドとする
    /// </summary>
    public record FieldViewConstructData(
        Vector2 FieldViewPixelSize,
        Rect FieldViewRect,
        Vector2 FieldViewOriginPoint,
        float TierViewWidth,
        IReadOnlyDictionary<KomaId, Rect> KomaAreaDictionary)
    {
        public Vector2 FieldViewPixelSize { get; } = FieldViewPixelSize;                            // フィールド全体のピクセルサイズ
        public Rect FieldViewRect { get; } = FieldViewRect;                                         // フィールド全体の矩形（ワールド座標系）
        public Vector2 FieldViewOriginPoint { get; } = FieldViewOriginPoint;                        // フィールド上のオブジェクト（キャラとか）の原点（ワールド座標系）
        public float TierViewWidth { get; } = TierViewWidth;                                        // コマ1段分のフィールド幅（ワールド座標系）
        public IReadOnlyDictionary<KomaId, Rect> KomaAreaDictionary { get; } = KomaAreaDictionary;  // 各コマがフィールドのどの範囲にあたるか（ワールド座標系）
    }
}
