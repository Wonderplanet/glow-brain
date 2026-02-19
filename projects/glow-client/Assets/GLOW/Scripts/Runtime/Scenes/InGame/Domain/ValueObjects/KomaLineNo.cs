namespace GLOW.Scenes.InGame.Domain.ValueObjects
{
    public record KomaLineNo(int Value)
    {
        public static KomaLineNo Empty { get; } = new(0);

        public static KomaLineNo operator +(KomaLineNo a, int b)
        {
            if (a.IsEmpty()) return Empty;
            return new KomaLineNo(a.Value + b);
        }

        public static KomaLineNo operator- (KomaLineNo a, int b)
        {
            if (a.IsEmpty()) return Empty;
            return new KomaLineNo(a.Value - b);
        }

        public static KomaLineNo operator+ (KomaLineNo a, KomaLineNo b)
        {
            if (a.IsEmpty() || b.IsEmpty()) return Empty;
            return new KomaLineNo(a.Value + b.Value);
        }

        public static KomaLineNo operator- (KomaLineNo a, KomaLineNo b)
        {
            if (a.IsEmpty() || b.IsEmpty()) return Empty;
            return new KomaLineNo(a.Value - b.Value);
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    }
}
