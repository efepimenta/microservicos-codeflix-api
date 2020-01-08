import {HttpResource} from "./http-resource";
import {httpVideo} from "./index";

const genresHttp = new HttpResource(httpVideo, 'genres');

export default genresHttp;