namespace GLOW.Scenes.InGame.Presentation.Constants
{
    public static class FieldZPositionDefinitions
    {
        // ローカル座標に設定する場合、Rootの下にあるものは参照時にRootからの相対位置になるようRootの値を引いてから代入する
        // 現在SpecialAttackとUnitEscapeはUnitRootの下にあるため使用時は+40されることとなる(-990f, -1000fになる)
        public const float OutpostRoot = 0f;
        public const float DefenseTargetRoot = -10f;
        public const float GimmickObjectRoot = -20f;
        public const float PlacedItemObjectRoot = -30f;
        public const float UnitRoot = -40f;
        public const float SpecialAttack = -1030f;
        public const float UnitEscape = -1040f;
        public const float AttackRoot = -1050f;
        public const float EffectRoot = -1060f;
        public const float SpecialUnit = -1070f;
        public const float BlackCurtain = -1080f;
        public const float SpecialUnitSpecialAttack = -1090f;
        public const float Highlight = -1090f;
    }
}
