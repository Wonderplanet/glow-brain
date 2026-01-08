namespace GLOW.Scenes.PartyFormation.Domain.Constants
{
    /// <summary> パーティ編成にてユニット選択時に編成可能かチェック時に返す </summary>
    public enum PartyMemberAssignmentResultType
    {
        Valid,              // 編成可能
        NotEmpty,           // パーティ枠に空きが無い時
        SpecialUnitLimit,   // スペシャルユニット選択時にパーティでのスペシャルユニット制限数を超えた時
    }
}
