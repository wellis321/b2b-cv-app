declare module 'pdfmake/build/pdfmake' {
	const content: any;
	export default content;
}

declare module 'pdfmake/build/vfs_fonts' {
	const content: any;
	export default content;
}

declare module 'pdfmake/interfaces' {
	export interface TDocumentDefinitions {
		content: Content[];
		styles?: StyleDictionary;
		defaultStyle?: Style;
		pageSize?: PageSize;
		pageOrientation?: PageOrientation;
		pageMargins?: Margins;
		header?: DynamicContent | Function;
		footer?: DynamicContent | Function;
		background?: DynamicContent | Function;
		images?: { [key: string]: string };
		watermark?: Watermark;
		info?: DocumentInfo;
		compress?: boolean;
	}

	export interface DocumentInfo {
		title?: string;
		author?: string;
		subject?: string;
		keywords?: string;
		creator?: string;
		producer?: string;
	}

	export interface StyleDictionary {
		[key: string]: Style;
	}

	export interface Style {
		font?: string;
		fontSize?: number;
		fontFeatures?: FontFeature[];
		bold?: boolean;
		italics?: boolean;
		alignment?: Alignment;
		color?: string;
		columnGap?: number;
		fillColor?: string;
		decoration?: string | string[];
		decorationStyle?: string;
		decorationColor?: string;
		background?: string;
		lineHeight?: number;
		characterSpacing?: number;
		noWrap?: boolean;
		markerColor?: string;
		leadingIndent?: number;
		margin?: MarginArray | Margins;
		preserveLeadingSpaces?: boolean;
		opacity?: number;
	}

	export interface Margins {
		left?: number;
		right?: number;
		top?: number;
		bottom?: number;
	}

	export type MarginArray = [number, number, number, number];

	export interface Content {
		text?: string | Content[];
		style?: string | string[];
		columns?: Column[];
		stack?: Content[];
		image?: string;
		table?: Table;
		ul?: Content[];
		ol?: Content[];
		canvas?: CanvasElement[];
		qr?: string;
		pageBreak?: string;
		pageOrientation?: PageOrientation;
		pageSize?: PageSize;
		width?: number | string;
		height?: number | string;
		margin?: MarginArray | Margins;
		fit?: [number, number];
		alignment?: Alignment;
		[key: string]: any;
	}

	export interface Table {
		body: any[][];
		widths?: (string | number)[];
		heights?: (string | number)[];
		headerRows?: number;
		dontBreakRows?: boolean;
		keepWithHeaderRows?: number;
		layout?: TableLayout;
	}

	export interface TableLayout {
		hLineWidth?: Function;
		vLineWidth?: Function;
		hLineColor?: Function;
		vLineColor?: Function;
		fillColor?: Function;
		paddingLeft?: Function;
		paddingRight?: Function;
		paddingTop?: Function;
		paddingBottom?: Function;
	}

	export interface Column {
		text?: string;
		width?: number | string;
		stack?: Content[];
		columns?: Column[];
		image?: string;
		[key: string]: any;
	}

	export interface CanvasElement {
		type: string;
		x1?: number;
		y1?: number;
		x2?: number;
		y2?: number;
		x?: number;
		y?: number;
		r?: number;
		r1?: number;
		r2?: number;
		points?: { x: number; y: number }[];
		lineWidth?: number;
		lineColor?: string;
		fillOpacity?: number;
		dash?: { length: number; space?: number }[];
		linearGradient?: string[];
		color?: string;
	}

	export interface Watermark {
		text: string;
		color: string;
		opacity: number;
		angle: number;
	}

	export interface FontFeature {
		[key: string]: any;
	}

	export type PageSize = string | { width: number; height: number };
	export type PageOrientation = 'portrait' | 'landscape';
	export type DynamicContent = Content | Content[];
	export type Alignment = 'left' | 'right' | 'center' | 'justify';
}
